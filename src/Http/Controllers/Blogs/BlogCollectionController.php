<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\UpdateCollectionsRequest;
use GetCandy\Api\Http\Resources\Collections\CollectionCollection;

class BlogCollectionController extends BaseController
{
    /**
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateCollectionsRequest  $request
     * @return array
     */
    public function update($blog, UpdateCollectionsRequest $request)
    {
        try {
            $collections = GetCandy::blogCollections()->update($blog, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new CollectionCollection($collections);
    }

    /**
     * Deletes a blogs collection.
     *
     * @param  string  $blogId
     * @param  string  $collectionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($blogId, $collectionId)
    {
        $result = GetCandy::blogCollections()->delete($blogId, $collectionId);

        if ($result) {
            return response()->json([
                'message' => 'Successfully removed collection from blog',
            ], 202);
        }

        return response()->json('Error', 500);
    }
}
