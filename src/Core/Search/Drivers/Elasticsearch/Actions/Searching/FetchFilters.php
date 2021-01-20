<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Attributes\Actions\FetchAttribute;
use GetCandy\Api\Core\Channels\Actions\FetchCurrentChannel;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Filters\ChannelFilter;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Filters\CategoryFilter;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Filters\CustomerGroupFilter;

class FetchFilters extends Action
{
    protected $filterNamespace = 'GetCandy\Api\Core\Search\Drivers\Elasticsearch\Filters';

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
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
            'filters' => 'array|min:0',
            'category' => 'array|min:0',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle()
    {
        $currentChannel = FetchCurrentChannel::run();

        $applied = collect([
            (new CustomerGroupFilter)->process($this->user()),
            (new ChannelFilter)->process($currentChannel),
        ]);

        if (! empty($this->category)) {
            $applied->push(
                (new CategoryFilter)->process($this->category)
            );
        }
        foreach ($this->filters ?? [] as $filter => $value) {
            $object = $this->findFilter($filter);
            if ($object && $object = $object->process($value, $filter)) {
                $applied->push($object);
            }
        }

        return $applied;
    }

    /**
     * Find and create instance of filter if it exists.
     *
     * @param string $type Filter type
     *
     * @return mixed
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function findFilter($type)
    {
        // Is this an attribute filter?
        if ($attribute = FetchAttribute::run([
            'handle' => $type,
        ])) {
            $type = $attribute->type;
        }

        $name = ucfirst(camel_case(str_singular($type))).'Filter';
        $classname = $this->filterNamespace."\\{$name}";

        if (class_exists($classname)) {
            return app()->make($classname);
        } else {
            return app()->make("{$this->filterNamespace}\TextFilter");
        }
    }
}
