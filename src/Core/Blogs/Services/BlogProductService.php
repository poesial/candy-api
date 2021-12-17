<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class BlogProductService extends BaseService
{
    public function __construct()
    {
        $this->model = new Blog();
    }

    public function update($blog, array $data)
    {
        $blog = $this->getByHashedId($blog);
        $productIds = GetCandy::products()->getDecodedIds($data['products']);

        $products = collect($productIds)->mapWithKeys(function ($id, $index) {
            return [$id => ['position' => $index + 1]];
        });

        $blog->products()->sync($products);
        event(new IndexableSavedEvent($blog));

        return $blog->products;
    }

    public function attach($product, array $blogs)
    {
        $product = GetCandy::products()->getByHashedId($product);

        $id = $this->getDecodedIds($blogs);

        $product->blogs()->attach($id);

        foreach ($this->getByHashedIds($blogs) as $blog) {
            IndexObjects::run([
                'documents' => $blog,
            ]);
        }

        return $product;
    }

    public function delete($blogId, $productId)
    {
        $blog = $this->getByHashedId($blogId);
        $productId = GetCandy::products()->getDecodedId($productId);
        $blog->products()->detach($productId);
        event(new IndexableSavedEvent($blog));

        return $blog->products;
    }
}
