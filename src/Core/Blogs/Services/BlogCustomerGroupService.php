<?php

namespace GetCandy\Api\Core\Blogs\Services;

use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroup;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\BaseService;

class BlogCustomerGroupService extends BaseService
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
    public function store($blog, $groups)
    {
        $blog = $this->getByHashedId($blog);
        $groupData = [];
        foreach ($groups as $group) {
            $groupModel = FetchCustomerGroup::run([
                'encoded_id' => $group['id'],
            ]);
            $groupData[$groupModel->id] = [
                'visible' => $group['visible'],
                'purchasable' => $group['purchasable'],
            ];
        }
        $blog->customerGroups()->sync($groupData);
        $blog->load('customerGroups');

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
