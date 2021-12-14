<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Blogs\Models\BlogAssociation;
use GetCandy\Api\Core\Scaffold\BaseService;

class BlogAssociationService extends BaseService
{
    /**
     * @var \GetCandy\Api\Core\Blogs\Models\BlogAssociation
     */
    protected $associations;

    public function __construct()
    {
        $this->model = new Blog;
        $this->associations = new BlogAssociation;
    }

    /**
     * Stores a blog association.
     *
     * @param  string  $blog
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function store($blog, $data)
    {
        $blog = $this->getByHashedId($blog);

        $blog->associations()->delete();

        foreach ($data['relations'] as $index => $relation) {
            $relation['association'] = $this->getByHashedId($relation['association_id']);
            $relation['type'] = GetCandy::associationGroups()->getByHashedId($relation['type']);
            $assoc = new BlogAssociation;
            $assoc->group()->associate($relation['type']);
            $assoc->association()->associate($relation['association']);
            $assoc->parent()->associate($blog);
            $assoc->save();
        }

        return $blog->associations;
    }

    /**
     * Destroys blog association/s.
     *
     * @param  string  $blog
     * @param  array|string  $association
     * @return bool
     */
    public function destroy($blog, $association)
    {
        $blog = $this->getByHashedId($blog);

        if (is_array($association)) {
            $ref = $this->getDecodedIds($association);
            $blog->associations()->whereIn('association_id', $ref)->get()->delete();
        } else {
            $ref = $this->getDecodedId($association);
            $blog->associations()->where('association_id', '=', $ref)->first()->delete();
        }

        return true;
    }
}
