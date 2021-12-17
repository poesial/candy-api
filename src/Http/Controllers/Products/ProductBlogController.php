<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateBlogsRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogCollection;

class ProductBlogController extends BaseController
{
    /**
     * Handles the request to update a products categories.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Products\UpdateBlogsRequest  $request
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogCollection
     */
    public function update($product, UpdateBlogsRequest $request)
    {
        $blogs = GetCandy::productBlogs()->update($product, $request->all());

        return new BlogCollection($blogs);
    }

    /**
     * Deletes a product's category.
     *
     * @param  string  $productId
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($productId, $categoryId)
    {
        $result = GetCandy::productBlogs()->delete($productId, $categoryId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed category from product',
                'categoryName' => 'test',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
