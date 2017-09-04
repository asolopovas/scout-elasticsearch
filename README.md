# Scout-Elasticsearch Engine for Elasticsearch 5.*

### Version
3.0.0

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

3) Setup Elasticsearch evnironment variablies in your .env file. (Note: Replace these variables according to your configuration)
```
...
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