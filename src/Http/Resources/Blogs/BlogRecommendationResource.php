<?php

namespace GetCandy\Api\Http\Resources\Blogs;

use GetCandy\Api\Http\Resources\AbstractResource;

class BlogRecommendationResource extends AbstractResource
{
    public function payload()
    {
        return [];
    }

    public function includes()
    {
        return [
            'blog' => ['data' => new BlogResource($this->whenLoaded('blog'))],
        ];
    }
}
