<?php

namespace GetCandy\Api\Http\Resources\GoodFor;

use GetCandy\Api\Http\Resources\AbstractCollection;

class GoodForCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = GoodForResource::class;
}
