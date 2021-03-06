<?php
namespace Asolopovas\ScoutEngines;

use Elasticsearch\Client as Elasticsearch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;

class ElasticsearchEngine extends Engine
{
    /**
     * The Elasticsearch client instance.
     *
     * @var \Elasticsearch\Client
     */
    protected $elasticsearch;

    /**
     * The index name.
     *
     * @var string
     */
    protected $index;

    /**
     * Create a new engine instance.
     *
     * @param  \Elasticsearch\Client $elasticsearch
     * @return void
     */
    public function __construct(Elasticsearch $elasticsearch, $index)
    {
        $this->elasticsearch = $elasticsearch;
        $this->index         = $index;
    }

    /**
     * Update the given model in the index.
     *
     * @param  Collection $models
     * @return void
     */
    public function update($models)
    {
        $body = new BaseCollection();
        $models->each(function ($model) use ($body) {
            $array = $model->toSearchableArray();
            if (empty($array)) {
                return;
            }
            $body->push([
                'index' => [
                    '_index' => $this->index,
                    '_type'  => $model->searchableAs(),
                    '_id'    => $model->getKey(),
                ],
            ]);
            $body->push($array);
        });
        $this->elasticsearch->bulk([
            'refresh' => true,
            'body'    => $body->all(),
        ]);
    }

    /**
     * Remove the given model from the index.
     *
     * @param  Collection $models
     * @return void
     */
    public function delete($models)
    {
        $body = new BaseCollection();
        $models->each(function ($model) use ($body) {
            $body->push([
                'delete' => [
                    '_index' => $this->index,
                    '_type'  => $model->searchableAs(),
                    '_id'    => $model->getKey(),
                ],
            ]);
        });
        $this->elasticsearch->bulk([
            'refresh' => true,
            'body'    => $body->all(),
        ]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  Builder $query
     * @return mixed
     */
    public function search(Builder $query)
    {
        return $this->performSearch($query, [
            'filters' => $this->filters($query),
            'size'    => $query->limit ?: 10000,
        ]);
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  Builder $query
     * @param  int     $perPage
     * @param  int     $page
     * @return mixed
     */
    public function paginate(Builder $query, $perPage, $page)
    {
        $result = $this->performSearch($query, [
            'filters' => $this->filters($query),
            'size'    => $perPage,
            'from'    => (($page * $perPage) - $perPage),
        ]);
        $result['nbPages'] = (int) ceil($result['hits']['total'] / $perPage);

        return $result;
    }

    /**
     * Perform the given search on the engine.
     *
     * @param  Builder $builder
     * @param  array   $options
     * @return mixed
     */
    protected function performSearch(Builder $builder, array $options = [])
    {
        $filters = [];

        if (array_key_exists('filters', $options) && $options['filters']) {
            foreach ($options['filters'] as $field => $value) {
                $filters[] = [
                    'bool' => [
                        'must' => [
                            'match' => [
                                $field => [
                                    'query'    => $value,
                                    'operator' => 'AND',
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }

        $query = [
            'query' => [
                'bool' => [
                    'must'   => [
                        'query_string' => [
                            'query'            => $builder->query,
                            'default_operator' => 'AND',
                            'fuzziness'        => 1,
                        ],
                    ],
                    'filter' => $filters,
                ],
            ],
        ];

        $params = [
            'index' => $this->index,
            'type'  => $builder->model->searchableAs(),
            'body'  => $query,
        ];

        if (array_key_exists('size', $options)) {
            $params['size'] = $options['size'];
        }
        if (array_key_exists('from', $options)) {
            $params['from'] = $options['from'];
        }
        if ($builder->callback) {
            return call_user_func($builder->callback, $this->elasticsearch, $params);
        }
        
        return $this->elasticsearch->search($params);
    }

    /**
     * Get the filter array for the query.
     *
     * @param  Builder $query
     * @return array
     */
    protected function filters(Builder $query)
    {
        return $query->wheres;
    }

    /**
     * Map the given results to instances of the given model.
     *
     * @param  mixed                               $results
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return Collection
     */
    public function map($results, $model)
    {
        if (count($results['hits']) === 0) {
            return Collection::make();
        }
        $keys = collect($results['hits']['hits'])
            ->pluck('_id')
            ->values()
            ->all();
        $models = $model->whereIn($model->getQualifiedKeyName(), $keys)
            ->get()
            ->keyBy($model->getKeyName());

        return Collection::make($results['hits']['hits'])
            ->map(function ($hit) use ($model, $models) {
                return isset($models[$hit['_source'][$model->getKeyName()]]) ? $models[$hit['_source'][$model->getKeyName()]] : null;
            })
            ->filter()
            ->values();
    }

    /**
     *
     * Pluck and return the primary keys of the results.
     *
     * @param  mixed                            $results
     * @return \Illuminate\Support\Collection
     */
    public function mapIds($results)
    {
        return collect($results['hits']['hits'])
            ->pluck('_id')
            ->values()
            ->all();
    }

    /**
     * Get the total count from a raw result returned by the engine.
     *
     * @param  mixed $results
     * @return int
     */
    public function getTotalCount($results)
    {
        return $results['hits']['total'];
    }
}
