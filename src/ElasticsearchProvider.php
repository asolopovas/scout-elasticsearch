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
        $this->app->make(EngineManager::class)->extend('elasticsearch', function($app) {
            if (config('scout.elasticsearch.config.ssl.enabled')) {
                $client = ElasticBuilder::create()
                                        ->setHosts(config('scout.elasticsearch.config.hosts'))
                                        ->setSSLVerification(config('scout.elasticsearch.config.ssl.certificate'))
                                        ->build();
            } else {
                $client = ElasticBuilder::create()
                                        ->setHosts(config('scout.elasticsearch.config.hosts'))
                                        ->build();
            }

            return new ElasticsearchEngine($client, config('scout.elasticsearch.index'));
        });
    }
}
