<?php

namespace GetCandy\Api\Http\Requests\Blogs;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateUrlsRequest extends FormRequest
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
            'urls.*.slug' => 'required|unique:routes,slug',
        ];
    }
}
