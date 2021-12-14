<?php

namespace GetCandy\Api\Http\Resources\Blogs;

use GetCandy\Api\Core\Blogs\Factories\BlogVariantFactory;
use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Assets\AssetResource;
use GetCandy\Api\Http\Resources\Taxes\TaxResource;

class BlogVariantResource extends AbstractResource
{
    public function payload()
    {
        $factory = app()->getInstance()->make(BlogVariantFactory::class);

        // TODO: Wut? Yuck
        if ($this->resource->resource) {
            $this->resource->resource = $factory->init($this->resource->resource)->get(1, app()->request->user());
        } else {
            $this->resource = $factory->init($this->resource)->get(1, app()->request->user());
        }

        return [
            'id' => $this->encodedId(),
            'drafted_at' => $this->drafted_at,
            'sku' => $this->sku,
            'backorder' => $this->backorder,
            'requires_shipping' => (bool) $this->requires_shipping,
            'price' => $this->price,
            'factor_tax' => $this->factor_tax,
            'unit_price' => $this->unit_cost,
            'total_tax' => $this->total_tax,
            'unit_tax' => $this->unit_tax,
            'unit_qty' => $this->unit_qty,
            'min_qty' => $this->min_qty,
            'max_qty' => $this->max_qty,
            'min_batch' => $this->min_batch,
            'inventory' => $this->stock,
            'incoming' => $this->incoming,
            'group_pricing' => (bool) $this->group_pricing,
            'weight' => [
                'value' => $this->weight_value,
                'unit' => $this->weight_unit,
            ],
            'height' => [
                'value' => $this->height_value,
                'unit' => $this->height_unit,
            ],
            'width' => [
                'value' => $this->width_value,
                'unit' => $this->width_unit,
            ],
            'depth' => [
                'value' => $this->depth_value,
                'unit' => $this->depth_unit,
            ],
            'volume' => [
                'value' => $this->volume_value,
                'unit' => $this->volume_unit,
            ],
            'options' => $this->options,
            'cost' => $this->cost,
            'import_tax' => $this->import_tax,
            'inward_shipping_cost' => $this->inward_shipping_cost,
            'margin' => $this->margin,
            'profit' => $this->profit,
            'replenishment_arrival_date' => $this->replenishment_arrival_date,
            'replenishment_units' => $this->replenishment_units,
        ];
    }

    public function includes()
    {
        return [
            'blog' => ['data' => new BlogResource($this->whenLoaded('blog'), $this->only)],
            'image' => new AssetResource($this->whenLoaded('image')),
            'tiers' => new BlogTierCollection($this->whenLoaded('tiers')),
            'customer_pricing' => new BlogCustomerPriceCollection($this->whenLoaded('customerPricing')),
            'tax' => $this->include('tax', TaxResource::class),
            'draft' => $this->include('draft', self::class),
            'published_parent' => $this->include('publishedParent', self::class),
        ];
    }
}
