<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Core\Blogs\Services\BlogChannelService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\UpdateChannelRequest;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;

class BlogChannelController extends BaseController
{
    /**
     * Handles the request to update a blog's channels.
     *
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\UpdateChannelRequest  $request
     * @param  \GetCandy\Api\Core\Blogs\Services\BlogChannelService  $service
     * @return \GetCandy\Api\Http\Resources\Blogs\BlogResource
     */
    public function store($blog, UpdateChannelRequest $request, BlogChannelService $service)
    {
        $result = $service->store($blog, $request->get('channels', []));

        return new BlogResource($result);
    }

    /**
     * Handles the request to remove a blog's channel.
     *
     * @param  string  $blog
     * @param  mixed  $request (?)
     * @return void
     */
    public function destroy($blog, DeleteRequest $request)
    {
        GetCandy::blogAssociations()->destroy($blog, $request->associations);

        return $this->respondWithNoContent();
    }
}
