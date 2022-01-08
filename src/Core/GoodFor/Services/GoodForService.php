<?php

namespace GetCandy\Api\Core\GoodFor\Services;

use GetCandy;
use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;
use GetCandy\Api\Core\GoodFor\Interfaces\GoodForInterface;
use GetCandy\Api\Core\Scaffold\BaseService;
use \GetCandy\Api\Core\GoodFor\Models\GoodFor;
use Illuminate\Support\Facades\Log;

class GoodForService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\GoodFor\Models\GoodFor
     */
    protected $model;

    /**
     * The blog factory instance.
     *
     * @var GoodForInterface
     */
    protected $factory;

    public function __construct(BlogInterface $factory)
    {
        $this->model = new GoodFor();
        $this->modelProduct = new GetCandy\Api\Core\GoodFor\Models\GoodForProduct();
        $this->factory = $factory;
    }

    public function get()
    {
        return $this->model->query()->get();
    }

    public function create($data) {
        return $this->model->query()->create($data);
    }

    public function delete($id)
    {
        return $this->model->query()->where('id', $id)->delete();
    }
}
