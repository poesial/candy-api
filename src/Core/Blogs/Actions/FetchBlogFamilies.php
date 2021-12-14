<?php

namespace GetCandy\Api\Core\Blogs\Actions;

use GetCandy\Api\Core\Blogs\Models\BlogFamily;
use GetCandy\Api\Core\Blogs\Resources\BlogFamilyCollection;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchBlogFamilies extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->paginate = $this->paginate === null ?: $this->paginate;

        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'per_page' => 'numeric|max:200',
            'paginate' => 'boolean',
            'keywords' => 'nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $includes = $this->resolveEagerRelations();

        $query = BlogFamily::with($includes);

        if (! $this->paginate) {
            return $query->get();
        }

        if ($this->keywords) {
            $query->where('name', 'LIKE', "%{$this->keywords}%");
        }

        return $query->withCount(
                $this->resolveRelationCounts()
            )->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Blogs\Models\BlogFamily|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Blogs\Resources\BlogFamilyCollection
     */
    public function response($result, $request)
    {
        return new BlogFamilyCollection($result);
    }
}
