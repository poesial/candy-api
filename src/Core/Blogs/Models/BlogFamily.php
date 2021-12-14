<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Scaffold\BaseModel;

class BlogFamily extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'blog_family';

    protected $guarded = [];

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Get all of the attributes for the blog family.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function attributes()
    {
        return $this->morphToMany(Attribute::class, 'attributable')->orderBy('position', 'asc');
    }

    /**
     * Scope a query to only include the default record.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefault($query)
    {
        return $query;
    }
}
