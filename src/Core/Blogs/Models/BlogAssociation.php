<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Scaffold\BaseModel;

class BlogAssociation extends BaseModel
{
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
        'group_id',
        'association_id',
        'blog_id',
    ];

    /**
     * Get the parent blog associated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function parent()
    {
        return $this->belongsTo(Blog::class, 'blog_id');
    }

    public function association()
    {
        return $this->belongsTo(Blog::class, 'association_id');
    }

    public function group()
    {
        return $this->belongsTo(AssociationGroup::class);
    }
}
