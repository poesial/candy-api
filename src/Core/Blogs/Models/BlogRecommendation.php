<?php

namespace GetCandy\Api\Core\Blogs\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class BlogRecommendation extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['blog_id'];

    public $timestamps = false;

    public function blog()
    {
        return $this->belongsTo(Blog::class, 'related_blog_id');
    }
}
