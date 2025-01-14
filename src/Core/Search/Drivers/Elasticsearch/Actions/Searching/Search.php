<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Search as ElasticaSearch;
use GetCandy\Api\Core\Blogs\Models\Blog;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Actions\FetchSearchedIds;
use GetCandy\Api\Http\Resources\Blogs\BlogCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Action;

class Search extends Action
{
    /**
     * @var array
     */
    protected $topFilters = [
        'channel-filter',
        'customer-group-filter',
        'category-filter',
    ];

    protected $start;

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
            'index' => 'nullable|string',
            'limit' => 'nullable|numeric',
            'offset' => 'nullable|numeric',
            'search_type' => 'nullable|string',
            'filters' => 'nullable',
            'aggregate' => 'nullable|array',
            'term' => 'nullable|string',
            'language' => 'nullable|string',
            'page' => 'nullable|numeric|min:1',
            'category' => 'nullable|string',
        ];
    }

    /**
     * Execute the action and return a result.
     */
    public function handle()
    {
        $this->start = now();
        $this->set('search_type', $this->search_type ? Str::plural($this->search_type) : 'products');

        if (! $this->index) {
            $prefix = config('getcandy.search.index_prefix');
            $language = app()->getLocale();
            $index = Str::plural($this->search_type);
            $this->set('index', "{$prefix}_{$index}_{$language}");
        }

        // Handle request filters
        if (! $this->filters) {
            // Get our filterable attributes.
            $filterable = \GetCandy::attributes()->getFilterable()->pluck('handle')->toArray();
            $filterable[] = 'price';

            $this->filters = $this->only($filterable);
        } else {
            $this->filters = $this->filters ? collect(explode(',', $this->filters))->mapWithKeys(function ($filter) {
                [$label, $value] = explode(':', $filter);

                return [$label => $value];
            })->toArray() : [];
        }

        $this->aggregates = $this->aggregates ?: [];
        $this->language = $this->language ?: app()->getLocale();
        $this->set('category', $this->category ? explode(':', $this->category) : []);

        $client = FetchClient::run();

        $term = $this->term ? FetchTerm::run($this->attributes) : null;

        $filters = FetchFilters::run([
            'category' => $this->category,
            'filters' => $this->filters,
        ]);

        $limit = $this->limit ?: 100;

        $offset = (($this->page ?: 1) - 1) * $limit;

        $query = new Query();
        $query->setParam('size', $limit);
        $query->setParam('from', $offset);

        $boolQuery = new BoolQuery;

        if ($term) {
            $boolQuery->addMust($term);

            $query = SetSuggestion::run([
                'query' => $query,
                'term' => $this->term,
            ]);
        }

        $aggregations = FetchAggregations::run();

        $query = SetExcludedFields::run(['query' => $query]);

        // Set filters as post filters
        $postFilter = new BoolQuery;

        $preFilters = $filters->filter(function ($filter) {
            return in_array($filter->handle, $this->topFilters);
        });

        $preFilters->each(function ($filter) use ($boolQuery) {
            // dump($filter->getQuery());
            $boolQuery->addFilter(
                 $filter->getQuery()
             );
        });

        $postFilters = $filters->filter(function ($filter) {
            return ! in_array($filter->handle, $this->topFilters);
        });

        $postFilters->each(function ($filter) use ($postFilter, $query) {
            if (method_exists($filter, 'aggregate')) {
                $query->addAggregation(
                    $filter->aggregate()->getPost(
                        $filter->getValue()
                    )
                );
            }
            $postFilter->addFilter(
                $filter->getQuery()
            );
        });

        $query->setPostFilter($postFilter);

        // // $globalAggregation = new \Elastica\Aggregation\GlobalAggregation('all_products');
        foreach ($aggregations as $aggregation) {
            if (method_exists($aggregation, 'get')) {
                $query->addAggregation(
                     $aggregation->addFilters($postFilters)->get($postFilters)
                 );
                // $globalAggregation->addAggregation(
                     // $agg->addFilters($postFilters)->get($postFilters)
                 // );
            }
        }

        $query->setQuery($boolQuery);

        $query = SetSorting::run([
            'query' => $query,
            'type' => $this->search_type,
            'sort' => $this->sort,
        ]);

        $query->setHighlight(config('getcandy.search.highlight') ?? [
            'pre_tags' => ['<em class="highlight">'],
            'post_tags' => ['</em>'],
            'fields' => [
                '*' => [
                    'fragment_size' => 200,
                    'number_of_fragments' => 50,
                ],
            ],
        ]);

        $query = $query->setSource(false)->setStoredFields([]);

        $search = new ElasticaSearch($client);

        return $search
            ->addIndex(
                $this->index ?: config('getcandy.search.index')
            )
            ->setOption(
                ElasticaSearch::OPTION_SEARCH_TYPE,
                ElasticaSearch::OPTION_SEARCH_TYPE_DFS_QUERY_THEN_FETCH
            )->search($query);
    }

    /**
     * @param $result
     * @param $request
     *
     * @return CategoryCollection|ProductCollection
     */
    public function jsonResponse($result, $request)
    {
        $ids = collect();
        $results = collect($result->getResults());

        if ($results->count()) {
            foreach ($results as $r) {
                $ids->push($r->getId());
            }
        }

        $aggregations = MapAggregations::run([
            'aggregations' => $result->getAggregations(),
        ]);

        $type = $this->search_type;
        switch ($type) {
            case 'products':
                $type = Product::class;
                break;
            case 'categories':
                $type = Category::class;
                break;
            case 'blogs':
                $type = Blog::class;
                break;
            default:
                break;
        }
        $models = FetchSearchedIds::run([
            'model' => $type,
            'encoded_ids' => $ids->toArray(),
            'include' => $request->include,
            'counts' => $request->counts,
        ]);


        $resource = ProductCollection::class;

        if ($this->search_type == 'categories') {
            $resource = CategoryCollection::class;
        }

        if ($this->search_type == 'blogs') {
            $resource = BlogCollection::class;
        }

        /**
         * If we return a lot of records Elasticsearch won't like paginating
         * so if it's over 10000 returned, set a hard limit.
         */
        $totalHits = $result->getTotalHits();
        $paginator = new LengthAwarePaginator(
            $models,
            $totalHits >= 1000 ? 1000 : $totalHits,
            $result->getQuery()->getParam('size'),
            $this->page ?: 1
        );

        $response = (new $resource($paginator))->additional([
            'meta' => [
                'count' => $models->count(),
                'large_dataset' => $totalHits >= 1000,
                'aggregations' => $aggregations,
                'highlight' => $result->getQuery()->getParam('highlight'),
            ],
        ]);

        return $response;
    }
}
