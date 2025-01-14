<?php

namespace GetCandy\Api\Http\Resources\Products;

use GetCandy\Api\Core\Channels\Resources\ChannelCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupCollection;
use GetCandy\Api\Core\Products\Resources\ProductFamilyResource;
use GetCandy\Api\Core\Routes\Resources\RouteCollection;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Assets\AssetResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Blogs\BlogCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountModelCollection;
use GetCandy\Api\Http\Resources\GoodFor\GoodForCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;
use GetCandy\Api\Http\Resources\Versioning\VersionCollection;

class ProductResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'drafted_at' => $this->drafted_at,
            'option_data' => $this->parseOptionData($this->option_data),
            'variants_count' => $this->variants_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function optional()
    {
        return [

        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'draft' => $this->include('draft', self::class),
            'layout' => $this->include('layout', LayoutResource::class),
            'published_parent' => $this->include('publishedParent', self::class),
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'family' => $this->include('family', ProductFamilyResource::class),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'channels' => new ChannelCollection($this->whenLoaded('channels'), $this->only),
            'first_variant' => $this->include('firstVariant', ProductVariantResource::class),
            'primary_asset' => $this->include('primaryAsset', AssetResource::class),
            'categories' => new CategoryCollection($this->whenLoaded('categories'), $this->only),
            'blogs' => new BlogCollection($this->whenLoaded('blogs'), $this->only),
            'good_for_icons' => new GoodForCollection($this->whenLoaded('goodForIcons'), $this->only),
            'variants' => new ProductVariantCollection($this->whenLoaded('variants'), $this->only),
            'discounts' => new DiscountModelCollection($this->whenLoaded('discounts'), $this->only),
            'collections' => new CollectionCollection($this->whenLoaded('collections'), $this->only),
            'associations' => new ProductAssociationCollection($this->whenLoaded('associations'), $this->only),
            'versions' => new VersionCollection($this->whenLoaded('versions'), $this->only),
            'customer_groups' => new CustomerGroupCollection($this->whenLoaded('customerGroups')),
        ];
    }

    protected function parseOptionData($data)
    {
        $data = $this->sortOptions($data);
        foreach ($data as $optionKey => $option) {
            $data[$optionKey]['options'] = collect($option['options'] ?? [])->mapWithKeys(function ($option, $handle) {
                $option['handle'] = $handle;

                return [$handle => $option];
            })->toArray();
        }

        return $data;
    }

    protected function sortOptions($options)
    {
        $options = $options ?? [];

        uasort($options, function ($a, $b) {
            return $a['position'] < $b['position'] ? -1 : 1;
        });

        return $options;
    }
}
