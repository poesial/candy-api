<?php

namespace GetCandy\Api\Http\Resources\Blogs;

use GetCandy\Api\Core\Channels\Resources\ChannelCollection;
use GetCandy\Api\Core\Customers\Resources\CustomerGroupCollection;
use GetCandy\Api\Core\Blogs\Resources\BlogFamilyResource;
use GetCandy\Api\Core\Routes\Resources\RouteCollection;
use GetCandy\Api\Http\Controllers\Blogs\BlogController;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetCollection;
use GetCandy\Api\Http\Resources\Assets\AssetResource;
use GetCandy\Api\Http\Resources\Attributes\AttributeCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;
use GetCandy\Api\Http\Resources\Discounts\DiscountModelCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Versioning\VersionCollection;

class BlogResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'drafted_at' => $this->drafted_at,
            'option_data' => $this->parseOptionData($this->option_data),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function optional()
    {
        return [

        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
            'draft' => $this->include('draft', self::class),
            'layout' => $this->include('layout', LayoutResource::class),
            'published_parent' => $this->include('publishedParent', self::class),
            'assets' => new AssetCollection($this->whenLoaded('assets')),
            'family' => $this->include('family', BlogFamilyResource::class),
            'routes' => new RouteCollection($this->whenLoaded('routes')),
            'primary_asset' => $this->include('primaryAsset', AssetResource::class),
            'categories' => new CategoryCollection($this->whenLoaded('categories'), $this->only),
            'products' => new ProductCollection($this->whenLoaded('products'), $this->only),
            'associations' => new BlogAssociationCollection($this->whenLoaded('associations'), $this->only),
            'versions' => new VersionCollection($this->whenLoaded('versions'), $this->only),
        ];
    }

    protected function parseOptionData($data)
    {
        $data = $this->sortOptions($data);
        foreach ($data as $optionKey => $option) {
            $data[$optionKey]['options'] = collect($option['options'] ?? [])->mapWithKeys(function ($option, $handle) {
                $option['handle'] = $handle;

                return [$handle => $option];
            })->toArray();
        }

        return $data;
    }

    protected function sortOptions($options)
    {
        $options = $options ?? [];

        uasort($options, function ($a, $b) {
            return $a['position'] < $b['position'] ? -1 : 1;
        });

        return $options;
    }
}
