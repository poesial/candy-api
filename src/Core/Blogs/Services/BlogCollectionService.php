<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\BaseService;

class BlogCollectionService extends BaseService
{
    public function __construct()
    {
        $this->model = new Blog();
    }

    public function update($blog, array $data)
    {
        $blog = $this->getByHashedId($blog);
        $collection_ids = GetCandy::collections()->getDecodedIds($data['collections']);
        $blog->collections()->sync($collection_ids);

        return $blog->collections;
    }

    public function delete($blogId, $collectionId)
    {
        $blog = $this->getByHashedId($blogId);
        $collectionId = GetCandy::collections()->getDecodedId($collectionId);
        $blog->collections()->detach($collectionId);

        return $blog->collections;
    }
}
