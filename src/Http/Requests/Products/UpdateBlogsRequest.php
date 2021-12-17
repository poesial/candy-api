<?php

namespace GetCandy\Api\Http\Requests\Products;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateBlogsRequest extends FormRequest
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
            'blogs' => 'required|array|min:1',
            'blogs.*' => 'required|hashid_is_valid:GetCandy\Api\Core\Blogs\Models\Blog',
        ];
    }

    public function attributes()
    {
        return [
            'blogs.*' => 'blog',
        ];
    }
}
