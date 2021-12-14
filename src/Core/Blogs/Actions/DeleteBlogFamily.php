<?php

namespace GetCandy\Api\Core\Blogs\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;

class DeleteBlogFamily extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-blog-families');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return bool
     */
    public function handle()
    {
        $blogFamily = $this->delegateTo(FetchBlogFamily::class);

        return $blogFamily->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   bool
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        return $this->respondWithNoContent();
    }
}
