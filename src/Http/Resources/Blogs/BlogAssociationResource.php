<?php

namespace GetCandy\Api\Http\Resources\Blogs;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Associations\AssociationGroupResource;

class BlogAssociationResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }

    public function includes()
    {
        return [
            'association' => ['data' => new BlogResource($this->whenLoaded('association'), $this->only)],
            'group' => ['data' => new AssociationGroupResource($this->whenLoaded('group'), $this->only)],
        ];
    }
}
