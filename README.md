# Scout-Elasticsearch Engine for Elasticsearch 5.*

### Version
3.0.1

## Contents
- [Installation](#installation)
- [License](#license)
- [Requirements](#requirements)
- [Issues](#issues)

## Installation

1) Download package via composer
```
composer require asolopovas/scout-elasticsearch
```

2) Add Scout service provider to ./config/app.php
```php
'providers' => [
    ...
    Laravel\Scout\ScoutServiceProvider::class,
    ...
],
```

3) Append 'elasticsearch' configuration to ./config/scout.php:

```php
    'elasticsearch' => [
        'index'  => env('ELASTICSEARCH_INDEX', 'laravel'),
        'config' => [
            'ssl'  => env('ELASTICSEARCH_SSL', false),
            'hosts' => [
                env('ELASTICSEARCH_HOST', "http://localhost:9200"),
            ],
            'ssl'   => [
                'certificate' => resource_path().'/ssl/ca.crt',
            ],
        ],
    ],
```

4) Setup Elasticsearch evnironment variablies in your .env file. (Note: Replace these variables according to your configuration, basic auth can be passed as this http://user:pass@localhost. If you set SSL to true don't forget to add you verification certificate to ./resources/ssl)

```
...
ELASTICSEARCH_SSL=false
ELASTICSEARCH_HOST=http://localhost
ELASTICSEARCH_INDEX=laravel
...
```

## Requirements
Scout-Elasticsearch engine require PHP version >=5.6.6. As well as Elasticsearch server up and running. 

## Issues
Bug reports and feature requests can be submitted on the [Github Issue Tracker](https://github.com/asolopovas/scout-elasticsearch/issues)

## License
Open-Sourced software licensed under the MIT license
