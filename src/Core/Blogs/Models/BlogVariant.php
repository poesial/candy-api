<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\Lockable;
use NeonDigital\Drafting\Draftable;

class BlogVariant extends BaseModel
{
    use HasAttributes, Lockable, Draftable;

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
    protected $fillable = [
        'asset_id',
        'blog_id',
        'options',
        'price',
        'incoming',
        'sku',
        'stock',
        'backorder',
        'incoming',
        'unit_qty',
        'min_qty',
        'max_qty',
        'min_batch',
        'tax_id',
        'cost',
        'import_tax',
        'inward_shipping_cost',
        'margin',
        'profit',
        'replenishment_arrival_date',
        'replenishment_units',
        'weight_value',
        'weight_unit',
        'height_value',
        'height_unit',
        'width_value',
        'width_unit',
        'depth_value',
        'depth_unit',
        'volume_value',
        'volume_unit',
    ];

    protected $pricing;

    /**
     * Return the blog relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class)->withoutGlobalScopes();
    }

    /**
     * Return the blog relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function availableBlog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    /**
     * Return the basket lines.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function basketLines()
    {
        return $this->hasMany(BasketLine::class);
    }

    /**
     * Get the variant name attribute.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        //TODO: Figure out a more dynamic way to do this
        $name = '';
        $localeUsed = 'en';
        $locale = app()->getLocale();
        $i = 0;

        foreach ($this->options as $handle => $option) {
            if (! empty($option[$locale])) {
                $localeUsed = $locale;
            }
            $name .= $option[$localeUsed].($i == count($this->options) ? ', ' : '');
        }

        return $name;
    }

    public function getOptionsAttribute($val)
    {
        $values = [];
        $option_data = $this->blog ? $this->blog->option_data : [];
        if (! is_array($val)) {
            $val = json_decode($val, true);
        }
        foreach ($val ?? [] as $option => $value) {
            if (! empty($data = $option_data[$option])) {
                $values[$option] = $data['options'][$value]['values'] ?? [
                    'en' => null,
                ];
            }
        }

        return $values;
    }

    public function setOptionsAttribute($val)
    {
        $options = [];

        if (! is_array($val)) {
            $val = json_decode($val, true);
        }
        if (! $this->id) {
            foreach ($val as $option => $value) {
                if (is_array($value)) {
                    $value = reset($value);
                }
                $options[str_slug($option)] = str_slug($value);
            }
            $this->attributes['options'] = json_encode($options);
        } else {
            $this->attributes['options'] = $val;
        }
    }

    public function image()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function customerPricing()
    {
        return $this->hasMany(BlogCustomerPrice::class);
    }

    public function tiers()
    {
        return $this->hasMany(BlogPricingTier::class);
    }
}
