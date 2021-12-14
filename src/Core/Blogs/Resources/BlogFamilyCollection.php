<?php

namespace GetCandy\Api\Core\Blogs\Resources;

use GetCandy\Api\Http\Resources\AbstractCollection;

class BlogFamilyCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = BlogFamilyResource::class;

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'meta' => [
                'key' => 'value',
            ],
        ];
    }
}
