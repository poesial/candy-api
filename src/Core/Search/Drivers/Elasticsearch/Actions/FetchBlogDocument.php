<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions;

use Illuminate\Support\Facades\Log;

class FetchBlogDocument extends AbstractDocumentAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
//
//        if (app()->runningInConsole()) {
//            return true;
//        }
//
//        return $this->user()->can('index-documents');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'model' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->getIndexables($this->model);
    }
}
