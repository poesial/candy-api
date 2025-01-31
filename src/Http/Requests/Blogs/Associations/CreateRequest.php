<?php

namespace GetCandy\Api\Http\Requests\Blogs\Associations;

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
            'relations.*.association_id' => 'required|hashid_is_valid:blogs',
            'relations.*.type' => 'required|hashid_is_valid:association_groups',
            'relations' => 'array|min:0',
        ];
    }
}
