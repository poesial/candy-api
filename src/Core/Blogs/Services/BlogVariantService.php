<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Blogs\Factories\BlogVariantFactory;
use GetCandy\Api\Core\Blogs\Models\BlogVariant;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class BlogVariantService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Blogs\Factories\BlogVariantFactory
     */
    protected $factory;

    public function __construct(BlogVariantFactory $factory)
    {
        $this->model = new BlogVariant();
        $this->factory = $factory;
    }

    /**
     * Creates variants for a blog.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function create($id, array $data)
    {
        $blog = GetCandy::blogs()->getByHashedId($id, true);

        // If we are adding a new set of variants, get rid.

        if ($blog->variants->count() == 1) {
            foreach ($blog->variants as $variant) {
                $variant->basketLines()->delete();
                $variant->customerPricing()->delete();
                $variant->tiers()->delete();
            }
            $blog->variants()->delete();
        }

        $options = $blog->option_data;

        if (empty($options)) {
            $blog->update([
                'option_data' => $data['options'],
            ]);
            $options = $data['options'];
        }

        foreach ($data['variants'] as $newVariant) {
            $options = $this->mapOptions($options, $newVariant['options']);
            $sku = $newVariant['sku'];
            $i = 1;
            while (GetCandy::blogVariants()->existsBySku($sku)) {
                $sku = $sku.$i;
                $i++;
            }

            $variant = $blog->variants()->create([
                'price' => $newVariant['price'],
                'sku' => trim($sku),
                'stock' => $newVariant['inventory'],
                'options' => $newVariant['options'],
            ]);

            if (! empty($newVariant['tax_id'])) {
                $variant->tax()->associate(
                    GetCandy::taxes()->getByHashedId($newVariant['tax_id'])
                );
            } else {
                $variant->tax()->associate(
                    GetCandy::taxes()->getDefaultRecord()
                );
            }

            $this->setMeasurements($variant, $newVariant);

            $variant->save();

            if (! empty($newVariant['pricing'])) {
                $this->setGroupPricing($variant, $newVariant['pricing']);
            }

            if (! empty($newVariant['tiers'])) {
                $this->setPricingTiers($variant, $newVariant['tiers']);
            }
        }

        if (empty($data['options'])) {
            $blog->update([
                'option_data' => $options,
            ]);
        }

        return $blog->load([
            'variants.customerPricing.group',
            'variants.customerPricing.tax',
            'variants.image.transforms',
            'variants.tax',
            'variants.tiers.group',
        ]);
    }

    public function canAddToBasket($variantId, $quantity)
    {
        $variant = $this->getByHashedId($variantId);

        $backorder = $variant->backorder;

        if ($backorder == 'always') {
            return true;
        }

        if ($backorder == 'expected') {
            return ($variant->incoming + $variant->stock) >= $quantity;
        }

        return $quantity <= $variant->stock;
    }

    /**
     * Checks whether a variant exists by its SKU.
     *
     * @param  string  $sku
     * @return bool
     */
    public function existsBySku($sku)
    {
        return $this->model->where('sku', '=', $sku)->exists();
    }

    public function getBySku($sku)
    {
        $variant = $this->model->where('sku', '=', $sku)->first();

        return $this->factory->init($variant)->get();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string  $hashedId
     * @param  array  $data
     * @return \GetCandy\Api\Core\Blogs\Models\BlogVariant
     *
     * @throws \Exception
     */
    public function update($hashedId, array $data)
    {
        $variant = $this->getByHashedId($hashedId);

        $options = $variant->blog->option_data;

        // Get the blog variants
        $variants = $variant->blog->variants;

        if (! empty($data['asset_id'])) {
            $data['asset_id'] = (new Asset)->decodeId($data['asset_id']);
        }

        $variant->fill($data);

        $thumbnailId = null;

        // if (! empty($data['image'])) {
        //     $imageId = $data['image']['id'];
        // } elseif (! empty($data['image_id'])) {
        //     $imageId = $data['image_id'];
        // }

        // if (! empty($imageId)) {
        //     $asset = GetCandy::assets()->getByHashedId($imageId);
        //     $variant->image()->associate($asset);
        // }

        if (! empty($data['tax_id'])) {
            $variant->tax()->associate(
                GetCandy::taxes()->getByHashedId($data['tax_id'])
            );
        } else {
            $variant->tax()->dissociate();
        }

        $this->setMeasurements($variant, $data);

        if (isset($data['group_pricing']) && ! $data['group_pricing']) {
            $variant->customerPricing()->delete();
        }

        $variant->group_pricing = ! empty($data['group_pricing']);

        if (isset($data['inventory'])) {
            $variant->stock = $data['inventory'];
        }

        if (! empty($data['pricing'])) {
            $this->setGroupPricing($variant, $data['pricing']);
        } elseif (isset($data['pricing']) && ! count($data['pricing'])) {
            $variant->customerPricing()->delete();
        }

        if (! empty($data['tiers'])) {
            $this->setPricingTiers($variant, $data['tiers']);
        } else {
            $variant->tiers()->delete();
        }

        $options = [];

        foreach ($data['options'] ?? [] as $option => $value) {
            if (is_array($value)) {
                $value = reset($value);
            }
            $options[str_slug($option)] = str_slug($value);
        }

        $variant->options = json_encode($options);

        // $this->attributes['options'] = json_encode($options);
        $variant->save();

        $variant->blog->update([
            'option_data' => $this->remapBlogOptions($variant, $data['options'] ?? []),
        ]);
        event(new IndexableSavedEvent($variant->blog));

        return $variant;
    }

    /**
     * Sets and creates the customer group pricing.
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\BlogVariant  $variant
     * @param  array  $prices
     * @return void
     */
    protected function setGroupPricing($variant, $prices = [])
    {
        $variant->customerPricing()->delete();

        foreach ($prices as $price) {
            $price['customer_group_id'] = DecodeId::run([
                'model' => CustomerGroup::class,
                'encoded_id' => $price['customer_group_id'],
            ]);

            if (! empty($price['tax_id'])) {
                $price['tax_id'] = GetCandy::taxes()->getDecodedId($price['tax_id']);
            } else {
                $price['tax_id'] = null;
            }

            $variant->customerPricing()->create($price);
        }
    }

    protected function setPricingTiers($variant, $tiers = [])
    {
        $variant->tiers()->delete();

        foreach ($tiers as $tier) {
            $tier['customer_group_id'] = DecodeId::run([
                'model' => CustomerGroup::class,
                'encoded_id' => $tier['customer_group_id'],
            ]);
            $variant->tiers()->create($tier);
        }
    }

    public function remapBlogOptions($variant, $incoming)
    {
        $optionsAvailable = [];
        $variants = $variant->blog()->first()->variants;
        $optionData = $this->mapOptions($variant->blog->option_data, $incoming);

        return $optionData;
    }

    /**
     * Map and merge variant options.
     *
     * @param  array  $options
     * @param  array  $newOptions
     * @return array
     */
    protected function mapOptions($options, $newOptions)
    {
        foreach ($newOptions as $handle => $option) {
            foreach ($option as $lang => $value) {
                $optionKey = str_slug($value);
                // If this is the first time this option is being set...
                if (empty($options[$handle])) {
                    $options[$handle]['label'][$lang] = title_case($value);
                }
                $options[$handle]['options'][$optionKey]['values'][$lang] = $value;
            }
        }

        return $options;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string  $hashedId
     * @return bool
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function delete($hashedId)
    {
        $variant = $this->getByHashedId($hashedId);

        if (! $variant) {
            abort(404);
        }

        $variant->customerPricing()->delete();
        $variant->tiers()->delete();
        $variant->basketLines()->delete();

        return $variant->delete();
    }

    /**
     * Maps and sets the measurements for a variant.
     * [
     *     'weight' => [
     *         'cm' => 100
     *     ]
     * ].
     *
     * @param  \GetCandy\Api\Core\Blogs\Models\BlogVariant  $variant
     * @param  array  $data
     * @return void
     */
    protected function setMeasurements($variant, $data)
    {
        $measurements = ['weight', 'height', 'width', 'depth', 'volume'];

        array_map(function ($x) use ($data, $variant) {
            if (! empty($data[$x])) {
                foreach ($data[$x] as $label => $value) {
                    $variant->setAttribute($x.'_'.$label, is_numeric($value) ? $value : $value);
                }
            }
        }, $measurements);
    }

    /**
     * Update a variants inventory.
     *
     * @param  string  $variantId
     * @param  int  $inventory
     * @return \GetCandy\Api\Core\Blogs\Models\BlogVariant
     */
    public function updateInventory($variantId, $inventory)
    {
        $variant = $this->getByHashedId($variantId);
        $variant->stock = $inventory;
        $variant->save();

        return $variant;
    }
}
