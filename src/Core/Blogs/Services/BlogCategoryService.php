<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class BlogCategoryService extends BaseService
{
    public function __construct()
    {
        $this->model = new Blog();
    }

    public function update($blog, array $data)
    {
        $blog = $this->getByHashedId($blog);
        $categoryIds = GetCandy::categories()->getDecodedIds($data['categories']);

        $categories = collect($categoryIds)->mapWithKeys(function ($id, $index) {
            return [$id => ['position' => $index + 1]];
        });

        $blog->categories()->sync($categories);
        event(new IndexableSavedEvent($blog));

        return $blog->categories;
    }

    public function attach($category, array $blogs)
    {
        $category = GetCandy::categories()->getByHashedId($category);

        $id = $this->getDecodedIds($blogs);

        $category->blogs()->attach($id);

        foreach ($this->getByHashedIds($blogs) as $blog) {
            IndexObjects::run([
                'documents' => $blog,
            ]);
        }

        return $category;
    }

    public function delete($blogId, $categoryId)
    {
        $blog = $this->getByHashedId($blogId);
        $categoryId = GetCandy::categories()->getDecodedId($categoryId);
        $blog->categories()->detach($categoryId);
        event(new IndexableSavedEvent($blog));

        return $blog->categories;
    }
}
