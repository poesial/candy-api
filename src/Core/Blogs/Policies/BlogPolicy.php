<?php

namespace GetCandy\Api\Core\Blogs\Policies;

use GetCandy\Api\Core\Blogs\Models\Blog;

class BlogPolicy
{
    public function before()
    {
        return true;
    }

    public function update(User $user, Blog $blog)
    {
        return true;
    }

    public function create(User $user, Blog $blog)
    {
        return true;
    }

    public function edit()
    {
        return true;
    }

    public function view()
    {
        return true;
    }
}
