<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaModel;
use GetCandy\Api\Core\Layouts\Models\Layout;
use GetCandy\Api\Core\Pages\Models\Page;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\RecycleBin\Traits\Recyclable;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Traits\HasCustomerGroups;
use GetCandy\Api\Core\Traits\HasRoutes;
use GetCandy\Api\Core\Traits\HasShippingExclusions;
use GetCandy\Api\Core\Traits\Indexable;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;
use NeonDigital\Drafting\Draftable;
use NeonDigital\Versioning\Versionable;
use Spatie\Activitylog\Traits\LogsActivity;

class Blog extends BaseModel
{
    use Assetable,
        HasAttributes,
        HasRoutes,
        Indexable,
        HasShippingExclusions,
        Draftable,
        LogsActivity,
        Versionable,
        Recyclable;

    /**
     * @var string
     */
    protected $settings = 'blogs';

    /**
     * @var int
     */
    protected $keepOldVersions = 10;

    /**
     * The blogs minimum price.
     *
     * @var int
     */
    public $min_price = 0;

    /**
     * The blogs maxiumum price.
     *
     * @var int
     */
    public $max_price = 0;

    /**
     * @var int
     */
    public $min_price_tax = 0;

    /**
     * @var int
     */
    public $max_price_tax = 0;

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The resource to use for API responses.
     *
     * @var string
     */
    public $resource = BlogResource::class;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'blog';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Sets the option data attribute
     * [
     *     [
     *         'label' => [
     *             'en' => 'Colour'
     *         ],
     *         'options' => [
     *             [
     *                 position: 1,
     *                 values: [
     *                     'en' => 'Espresso',
     *                     'fr' => 'Espresso'
     *                 ]
     *             ]
     *         ]
     *     ]
     * ].
     * @param  array  $value
     * @return void
     */
    public function setOptionDataAttribute($value)
    {
        $options = [];
        $parentPosition = 1;

        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        foreach ($value ?? [] as $option) {
            $label = reset($option['label']);
            $options[str_slug($label)] = $option;
            $childOptions = [];
            $position = 1;

            foreach ($option['options'] as $child) {
                $childLabel = reset($child['values']);
                $childOptions[str_slug($childLabel)] = $child;
                $childOptions[str_slug($childLabel)]['position'] = $position;
                $position++;
            }
            $options[str_slug($label)]['position'] = $parentPosition;
            $options[str_slug($label)]['options'] = $childOptions;
            $parentPosition++;
        }
        $this->attributes['option_data'] = json_encode($options);
    }

    public function getOptionDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Get the collections associated to the blog.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }

    /**
     * Get the related family.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function family()
    {
        return $this->belongsTo(BlogFamily::class, 'blog_family_id');
    }

    /**
     * Get the blogs page.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne(Page::class, 'element');
    }

    public function layout()
    {
        return $this->belongsTo(Layout::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'blog_categories')->withPivot('position');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'blog_products')->withPivot('position');
    }

    public function associations()
    {
        return $this->hasMany(BlogAssociation::class)->whereHas('association');
    }

    public function discounts()
    {
        return $this->morphMany(
            DiscountCriteriaModel::class,
            'eligible'
        )->with('criteria.set.discount');
    }

    public function recommendations()
    {
        return $this->hasMany(BlogRecommendation::class, 'related_blog_id');
    }

    public function getRecycleName()
    {
        return $this->name;
    }

    public function getRecycleThumbnail()
    {
        return $this->primaryAsset ? $this->primaryAsset->transforms->first()->url ?? null : null;
    }

    public function sizes()
    {
        return $this->hasMany(BlogSize::class);
    }

    public function colours()
    {
        return $this->hasMany(BlogColour::class);
    }
}
