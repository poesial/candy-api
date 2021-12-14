<?php

namespace GetCandy\Api\Core\Blogs\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;

class BlogFamilyResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'blogs_count' => (int) $this->blogs_count ?: 0,
            'attributes_count' => (int) $this->attributes_count ?: 0,
        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
        ];
    }
}
