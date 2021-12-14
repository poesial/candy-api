<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Core\Blogs\Services\BlogCustomerGroupService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\UpdateCustomerGroupsRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;

class BlogCustomerGroupController extends BaseController
{
    /**
     * Handles the request to update a blog's customer groups.
     *
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateCustomerGroupsRequest  $request
     * @param  \GetCandy\Api\Core\Blogs\Services\BlogCustomerGroupService  $service
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogResource
     */
    public function store($blog, UpdateCustomerGroupsRequest $request, BlogCustomerGroupService $service)
    {
        $result = $service->store($blog, $request->get('groups'));

        return new BlogResource($result);
    }

    /**
     * Handles the request to remove a blog association.
     *
     * @param  string  $blog
     * @param  mixed $request (?)
     * @return \Illuminate\Http\Response
     */
    public function destroy($blog, DeleteRequest $request)
    {
        GetCandy::blogAssociations()->destroy($blog, $request->associations);

        return $this->respondWithNoContent();
    }
}
