<?php

namespace GetCandy\Api\Core\Contents\Services;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Blogs\Actions\FetchBlogFamily;
use GetCandy\Api\Core\Blogs\Events\BlogCreatedEvent;
use GetCandy\Api\Core\Blogs\Interfaces\BlogInterface;
use GetCandy\Api\Core\Blogs\Models\BlogRecommendation;
use GetCandy\Api\Core\Routes\Actions\CreateRoute;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use Illuminate\Support\Facades\Log;
use \GetCandy\Api\Core\Contents\Models\Content;

class ContentService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Contents\Models\Content
     */
    protected $model;

    /**
     * The blog factory instance.
     *
     * @var \GetCandy\Api\Core\Contents\Interfaces\ContentInterface
     */
    protected $factory;

    public function __construct(BlogInterface $factory)
    {
        $this->model = new Content();
        $this->factory = $factory;
    }

    public function findBySlug($slug)
    {
        return $this->model->query()->whereSlug($slug)->first();
    }

    public function createBySlug($slug)
    {
        return $this->model->query()->create(['slug' => $slug]);
    }

    public function update($slug, $data)
    {
        return $this->model->query()->whereSlug($slug)->update($data);
    }
}
