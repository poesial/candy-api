<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Exceptions\InvalidLanguageException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\BlogVariants\CreateRequest;
use GetCandy\Api\Http\Requests\BlogVariants\DeleteRequest;
use GetCandy\Api\Http\Requests\BlogVariants\UpdateRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;
use GetCandy\Api\Http\Resources\Blogs\BlogVariantCollection;
use GetCandy\Api\Http\Resources\Blogs\BlogVariantResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogVariantController extends BaseController
{
    /**
     * Handles the request to show all blog variants.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogVariantCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::blogVariants()->getPaginatedData($request->per_page);

        return new BlogVariantCollection($paginator);
    }

    /**
     * Handles the request to show a blog variant based on hashed ID.
     *
     * @param  string  $id
     * @return array|\GetCandy\Api\Http\Resources\Blogs\BlogVariantResource
     */
    public function show($id)
    {
        try {
            $variant = GetCandy::blogVariants()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BlogVariantResource($variant);
    }

    /**
     * Handles the request to create the variants.
     *
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\BlogVariants\CreateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Blogs\BlogResource
     */
    public function store($blog, CreateRequest $request)
    {
        try {
            $result = GetCandy::blogVariants()->create($blog, $request->all());
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new BlogResource($result);
    }

    /**
     * Handles the request to update a blog variant.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\BlogVariants\UpdateRequest  $request
     * @return array|\GetCandy\Api\Http\Resources\Blogs\BlogVariantResource
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::blogVariants()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BlogVariantResource($result);
    }

    /**
     * Handles the request to delete a blog variant.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\BlogVariants\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::blogVariants()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    public function updateInventory($variant, Request $request)
    {
        try {
            $result = GetCandy::blogVariants()->updateInventory($variant, $request->inventory);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BlogVariantResource($result);
    }
}
