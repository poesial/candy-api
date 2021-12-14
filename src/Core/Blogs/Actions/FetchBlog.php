<?php

namespace GetCandy\Api\Core\Blogs\Actions;

use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Blogs\BlogResource;

class FetchBlog extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'integer|required_without_all:encoded_id,sku',
            'encoded_id' => 'string|hashid_is_valid:'.Blog::class.'|required_without_all:id,sku',
            'draft' => 'nullable|boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Blogs\Models\Blog|null
     */
    public function handle()
    {
        if ($this->encoded_id && ! $this->sku) {
            $this->id = (new Blog)->decodeId($this->encoded_id);
        }

        $query = Blog::query()
            ->withCount($this->resolveRelationCounts())
            ->with($this->resolveEagerRelations());

        if ($this->draft) {
            $query->withDrafted();
        }

        return $query->find($this->id);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Blogs\Models\Blog|null  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Blogs\Resources\BlogResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return (new BlogResource($result))->only($request->fields);
    }
}
