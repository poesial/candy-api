<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\UpdateProductsRequest;
use GetCandy\Api\Http\Resources\Products\ProductCollection;

class BlogProductController extends BaseController
{
    /**
     * Handles the request to update a blogs products.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateProductsRequest  $request
     * @return \GetCandy\Api\Http\Resources\Products\ProductCollection
     */
    public function update($blog, UpdateProductsRequest $request)
    {
        $products = GetCandy::blogProducts()->update($blog, $request->all());

        return new ProductCollection($products);
    }

    /**
     * Deletes a blog's product.
     *
     * @param  string  $blogId
     * @param  string  $productId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($blogId, $productId)
    {
        $result = GetCandy::blogProducts()->delete($blogId, $productId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed product from blog',
                'productName' => 'test',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
