<?php

namespace GetCandy\Api\Http\Requests\Blogs;

use GetCandy\Api\Http\Requests\FormRequest;

class CreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Blog::class);
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|valid_structure:blogs',
            'sub_title' => 'required|valid_structure:blogs',
            'url' => 'required|unique:routes,slug',
            'family_id' => 'required',
        ];
    }
}
