<?php

namespace GetCandy\Api\Http\Requests\Blogs\Associations;

use GetCandy\Api\Http\Requests\FormRequest;

class DeleteRequest extends FormRequest
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
            'associations' => 'required|hashid_is_valid:blogs',
        ];
    }
}
