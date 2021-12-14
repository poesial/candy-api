<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\UpdateAttributesRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogAttributeController extends BaseController
{
    /**
     * Handles the request to update a blogs attributes.
     *
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateAttributesRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Blogs\BlogResource
     */
    public function update($blog, UpdateAttributesRequest $request)
    {
        try {
            $result = GetCandy::blogs()->updateAttributes($blog, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new BlogResource($result);
    }
}
