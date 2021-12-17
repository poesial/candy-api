<?php

namespace GetCandy\Api\Http\Resources\Contents;

use GetCandy\Api\Http\Resources\AbstractResource;


class ContentResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'content' => $this->content,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
