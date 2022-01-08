<?php

namespace GetCandy\Api\Http\Resources\Brand;

use GetCandy\Api\Http\Resources\AbstractResource;
use Illuminate\Support\Facades\Storage;


class BrandResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'logo' => !empty($this->logo) ? Storage::url('brand/' . $this->logo ) : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
