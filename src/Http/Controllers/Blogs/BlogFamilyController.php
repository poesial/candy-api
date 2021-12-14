<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Core\Blogs\Criteria\BlogFamilyCriteria;
use GetCandy\Api\Exceptions\InvalidLanguageException;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\BlogFamilies\CreateRequest;
use GetCandy\Api\Http\Requests\BlogFamilies\DeleteRequest;
use GetCandy\Api\Http\Requests\BlogFamilies\UpdateRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogFamilyCollection;
use GetCandy\Api\Http\Resources\Blogs\BlogFamilyResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogFamilyController extends BaseController
{
    /**
     * Handles the request to show all blog families.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogFamilyCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::blogFamilies()->getPaginatedData(
            $request->per_page,
            $request->page ?: 1,
            $this->parseIncludes($request->includes),
            $request->keywords
        );
        // event(new ViewBlogEvent(['hello' => 'there']));
        return new BlogFamilyCollection($paginator);
    }

    /**
     * Handles the request to show a blog family based on hashed ID.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @param  \GetCandy\Api\Core\Blogs\Criteria\BlogFamilyCriteria  $criteria
     * @return array|\GetCandy\Api\Http\Resources\Blogs\BlogFamilyResource
     */
    public function show($id, Request $request, BlogFamilyCriteria $criteria)
    {
        try {
            $family = $criteria->id($id)->includes($this->parseIncludes($request->includes))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new BlogFamilyResource($family);
    }

    /**
     * Handles the request to create a new blog family.
     *
     * @param  \GetCandy\Api\Http\Requests\BlogFamilies\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        try {
            $blogFamily = GetCandy::blogFamilies()->create($request->all());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new BlogFamilyResource($blogFamily);
    }

    /**
     * Handles the request to update a blog family.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\BlogFamilies\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $blogFamily = GetCandy::blogFamilies()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        return new BlogFamilyResource($blogFamily);
    }

    /**
     * Handles the request to delete a blog family.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\BlogFamilies\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::blogFamilies()->delete($id, $request->blog_family_id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
