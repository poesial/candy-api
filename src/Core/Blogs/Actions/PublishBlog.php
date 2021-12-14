<?php

namespace GetCandy\Api\Core\Blogs\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;

class PublishBlog extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-blog');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Blogs\Models\BlogFamily
     */
    public function handle()
    {
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Blogs\Models\BlogFamily  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Blogs\Resources\BlogFamilyResource
     */
    public function response($result, $request)
    {
        return new BlogResource($result);
    }
}
