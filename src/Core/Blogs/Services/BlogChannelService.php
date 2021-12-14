<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\BaseService;

class BlogChannelService extends BaseService
{
    public function __construct()
    {
        $this->model = new Blog;
    }

    /**
     * Stores a blog association.
     *
     * @param  string  $blog
     * @param  array  $data
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function store($blog, $channels)
    {
        $blog = $this->getByHashedId($blog);
        $blog->channels()->sync(
            $this->getChannelMapping($channels)
        );
        $blog->load('channels');

        return $blog;
    }

    /**
     * Destroys blog customer groups.
     *
     * @param  string  $blog
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function destroy($blog)
    {
        $blog = $this->getByHashedId($blog);
        $blog->customerGroups()->detach();

        return $blog;
    }
}
