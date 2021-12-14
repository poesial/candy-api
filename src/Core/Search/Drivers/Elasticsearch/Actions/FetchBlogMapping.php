<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions;

use GetCandy\Api\Core\Search\Indexables\BlogIndexable;
use Lorisleiva\Actions\Action;

class FetchBlogMapping extends Action
{
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
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return array
     */
    public function handle()
    {
        return (new BlogIndexable)->getMapping();
    }
}
