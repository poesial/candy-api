<?php

namespace GetCandy\Api\Http\Requests\Blogs;

use GetCandy\Api\Http\Requests\FormRequest;

class UpdateProductsRequest extends FormRequest
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
            'products' => 'required|array|min:1',
            'products.*' => 'required|hashid_is_valid:GetCandy\Api\Core\Products\Models\Product',
        ];
    }

    public function attributes()
    {
        return [
            'products.*' => 'product',
        ];
    }
}
