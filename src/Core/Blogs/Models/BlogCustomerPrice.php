<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Scopes\BlogPricingScope;
use GetCandy\Api\Core\Taxes\Models\Tax;

class BlogCustomerPrice extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_group_id',
        'tax_id',
        'price',
        'compare_at_price',
        'blog_variant_id',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new BlogPricingScope);
    }

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'blog_family';

    public function variant()
    {
        return $this->belongsTo(BlogVariant::class);
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
