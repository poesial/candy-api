<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions;

use Elastica\Bulk;
use Elastica\Document;
use Elastica\Mapping;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Languages\Actions\FetchLanguages;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Events\IndexingCompleteEvent;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Action;

class IndexBlogs extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
//
//        if (app()->runningInConsole()) {
//            return true;
//        }
//
//        return $this->user()->can('index-documents');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'blogs' => 'required',
            'uuid' => 'required',
            'final' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */
    public function handle()
    {
        $client = FetchClient::run();

        $languages = FetchLanguages::run([
            'paginate' => false,
            'only_enabled' => true,
        ])->pluck('code');

        $customerGroups = FetchCustomerGroups::run([
            'paginate' => false,
        ]);

        $indexes = FetchIndex::run([
            'languages' => $languages->toArray(),
            'type' => 'blogs',
            'uuid' => $this->uuid,
        ]);

        $documents = [];

        foreach ($this->blogs as $blog) {
            $indexables = FetchBlogDocument::run([
                'model' => $blog,
            ]);


            foreach ($indexables as $document) {
                $documents[$document->lang][] = $document;
            }
        }

        foreach ($indexes as $index) {
            // If the index doesn't exist, then we update the mapping
            if (empty($index->actual->getMapping())) {
                $mapping = new Mapping();
                $mapping->setProperties(
                    FetchBlogMapping::run()
                );
                $mapping->send($index->actual);
            }
            // Get the documents for the index language.
            $docs = collect($documents[$index->language] ?? [])->map(function ($document) {
                return new Document($document->getId(), $document->getData());
            });

            if (! $docs->count()) {
                continue;
            }

            $bulk = new Bulk($client);
            $bulk->setIndex($index->actual);
            $bulk->addDocuments($docs->toArray());
            $bulk->send();
        }

        if ($this->final) {
            event(new IndexingCompleteEvent($indexes, 'blogs'));
        }

        // dd($index);
        // $this->timestamp = microtime(true);

        // dd($this->documents);

        // $aliases = $this->getNewAliases(new BlogIndexable, 'blogs');

        // $indiceNames = GetIndiceNamesAction::run([
        //     'filter' => $this->getNewIndexName()
        // ]);

        // foreach ($this->blogs as $blog) {
        //     $documents = (new BlogIndexable($blog))
        //         ->setIndexName($this->getNewIndexName())
        //         ->setSuffix($this->timestamp)
        //         ->getDocuments();
        //     dd($documents);
        // }
        // dd($this->blogs);
    }
}
