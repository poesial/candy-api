<?php

namespace GetCandy\Api\Http\Resources\GoodFor;

use GetCandy\Api\Http\Resources\AbstractResource;
use Illuminate\Support\Facades\Storage;


class GoodForResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon' => !empty($this->icon) ? Storage::url('good-for-icons/' . $this->icon ) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
