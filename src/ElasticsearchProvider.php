<?php
namespace Asolopovas\ScoutEngines;

use Elasticsearch\ClientBuilder as ElasticBuilder;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class ElasticsearchProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->app->make(EngineManager::class)->extend('elasticsearch', function ($app) {
            return new ElasticsearchEngine(ElasticBuilder::create()
                    ->setHosts(config('scout.elasticsearch.config.hosts'))
                    ->build(),
                config('scout.elasticsearch.index')
            );
        });
    }
}
