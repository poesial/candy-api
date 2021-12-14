<?php

namespace GetCandy\Api\Core\Blogs\Criteria;

use GetCandy\Api\Core\Blogs\Models\BlogFamily;
use GetCandy\Api\Core\Scaffold\AbstractCriteria;

class BlogFamilyCriteria extends AbstractCriteria
{
    public function getBuilder()
    {
        $family = new BlogFamily;
        $builder = $family->with($this->includes ?: []);
        if ($this->id) {
            $builder->where('id', '=', $family->decodeId($this->id));

            return $builder;
        }

        return $builder;
    }
}
