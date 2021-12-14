<?php

namespace GetCandy\Api\Core\Blogs;

use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;

class BlogCriteria extends AbstractCriteria
{
    /**
     * Gets the underlying builder for the query.
     *
     * @return \Illuminate\Database\Eloquent\QueryBuilder
     */
    public function getBuilder()
    {
        $blog = new Blog;
        $builder = $blog->with($this->includes ?: []);

        if (count($this->ids)) {
            return $builder->whereIn('id', $blog->decodeIds($this->ids));
        }

        if ($this->id) {
            $builder->where('id', '=', $blog->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
