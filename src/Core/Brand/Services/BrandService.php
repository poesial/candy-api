<?php

namespace GetCandy\Api\Core\Brand\Services;

use GetCandy;

use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;

use GetCandy\Api\Core\Scaffold\BaseService;

use \GetCandy\Api\Core\Brand\Models\Brand;

class BrandService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Brand\Models\Brand
     */
    protected $model;

    /**
     * The blog factory instance.
     *
     * @var \GetCandy\Api\Core\Brand\Interfaces\BrandInterface
     */
    protected $factory;

    public function __construct(BlogInterface $factory)
    {
        $this->model = new Brand();
        $this->factory = $factory;
    }

    public function first()
    {
        return $this->model->query()->first();
    }

    public function createFirst() {
        return $this->model->query()->create([]);
    }

    public function updateFirst($data)
    {
        return $this->model->query()->first()->update($data);
    }
}
