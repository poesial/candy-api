<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class DraftBlogAssociations extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-blogs');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'draft' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Blogs\Models\Blog
     */
    public function handle()
    {
        $this->parent->associations->each(function ($model) {
            $assoc = $model->replicate();
            $assoc->blog_id = $this->draft->id;
            $assoc->save();
        });

        return $this->draft;
    }
}
