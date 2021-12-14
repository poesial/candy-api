<?php

namespace GetCandy\Api\Http\Resources\Blogs;

use GetCandy\Api\Http\Resources\AbstractCollection;

class BlogTierCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = BlogTierResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
        ];
    }
}
