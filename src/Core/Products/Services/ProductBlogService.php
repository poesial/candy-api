<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Search\Actions\IndexObjects;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class ProductBlogService extends BaseService
{
    public function __construct()
    {
        $this->model = new Product();
    }

    public function update($product, array $data)
    {
        $product = $this->getByHashedId($product);
        $blogIds = GetCandy::blogs()->getDecodedIds($data['blogs']);

        $blogs = collect($blogIds)->mapWithKeys(function ($id, $index) {
            return [$id => ['position' => $index + 1]];
        });

        $product->blogs()->sync($blogs);
        event(new IndexableSavedEvent($product));

        return $product->blogs;
    }

    public function attach($blog, array $products)
    {
        $blog = GetCandy::blogs()->getByHashedId($blog);

        $id = $this->getDecodedIds($products);

        $blog->products()->attach($id);

        foreach ($this->getByHashedIds($products) as $product) {
            IndexObjects::run([
                'documents' => $product,
            ]);
        }

        return $blog;
    }

    public function delete($productId, $blogId)
    {
        $product = $this->getByHashedId($productId);
        $blogId = GetCandy::blogs()->getDecodedId($blogId);
        $product->blogs()->detach($blogId);
        event(new IndexableSavedEvent($product));

        return $product->blogs;
    }
}
