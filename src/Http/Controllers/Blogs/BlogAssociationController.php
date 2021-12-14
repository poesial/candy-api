<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\Associations\CreateRequest;
use GetCandy\Api\Http\Requests\Blogs\Associations\DeleteRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogAssociationCollection;

class BlogAssociationController extends BaseController
{
    /**
     * Handles the request to update a blogs associations.
     *
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\Associations\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogAssociationCollection
     */
    public function store($blog, CreateRequest $request)
    {
        $result = GetCandy::blogAssociations()->store($blog, $request->all());

        return new BlogAssociationCollection($result);
    }

    /**
     * Handles the request to remove a blog association.
     *
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\Associations\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($blog, DeleteRequest $request)
    {
        GetCandy::blogAssociations()->destroy($blog, $request->associations);

        return $this->respondWithNoContent();
    }
}
