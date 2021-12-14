<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\UpdateCategoriesRequest;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;

class BlogCategoryController extends BaseController
{
    /**
     * Handles the request to update a blogs categories.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateCategoriesRequest  $request
     * @return \GetCandy\Api\Http\Resources\Categories\CategoryCollection
     */
    public function update($blog, UpdateCategoriesRequest $request)
    {
        $categories = GetCandy::blogCategories()->update($blog, $request->all());

        return new CategoryCollection($categories);
    }

    /**
     * Deletes a blog's category.
     *
     * @param  string  $blogId
     * @param  string  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($blogId, $categoryId)
    {
        $result = GetCandy::blogCategories()->delete($blogId, $categoryId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed category from blog',
                'categoryName' => 'test',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
