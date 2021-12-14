<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\Lockable;
use NeonDigital\Drafting\Draftable;

class BlogColour extends BaseModel
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
        'name',
        'icon',
        'price',
    ];

    protected $pricing;

    /**
     * Return the blog relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }
}
