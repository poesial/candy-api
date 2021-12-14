<?php

namespace GetCandy\Api\Core\Blogs\Validators;

use GetCandy;

class BlogValidator
{
    public function available($attribute, $value, $parameter, $validator)
    {
        $variant = GetCandy::blogVariants()->getByHashedId($value);

        if (! $variant) {
            return false;
        }

        $validator->addReplacer('available', function ($message, $attribute, $rule, $parameters) use ($variant) {
            return trans('getcandy::validation.available', [
                'sku' => $variant->sku,
            ]);
        });

        return $variant->availableBlog()->exists();
    }
}
