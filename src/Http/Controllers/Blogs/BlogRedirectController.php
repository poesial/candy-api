<?php

namespace GetCandy\Api\Http\Controllers\Blogs;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Blogs\CreateUrlRequest;

class BlogRedirectController extends BaseController
{
    /**
     * @param  string  $blog
     * @param  \GetCandy\Api\Http\Requests\Blogs\CreateUrlRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store($blog, CreateUrlRequest $request)
    {
        GetCandy::blogs()->createUrl($blog, $request->all());

        return $this->respondWithNoContent();
    }
}
