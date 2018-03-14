# yii2_cached_purifier
Yii2 component that caches the results of [HTML Purifier](http://htmlpurifier.org/) in a cache that implements the [yii\caching\CacheInterface](http://www.yiiframework.com/doc-2.0/yii-caching-cacheinterface.html). This will remove malicious code (XSS) from strings and make it standards compliant. More information available at the [Yii2 Security best practices](http://www.yiiframework.com/doc-2.0/guide-security-best-practices.html#avoiding-xss)

## Requirements

-  "yiisoft/yii2": ">2.0.0"
-  "ezyang/htmlpurifier": "~4.9.3"
-  "php": ">=5.2"

## Install

Via Composer
``` bash
$ composer require linuskohl/yii2_cached_purifier dev-master
```
or add
```
"linuskohl/yii2_cached_purifier": "dev-master"
```
to the require section of your composer.json file.

## Configuration

To use this component, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'cache'  => [
            'class' => 'yii\redis\Cache',
            'redis' => [
                'hostname' => 'localhost',
                'port'     => 6379,
                'database' => 0,
            ]
        ],
        'cached_purifier' => [
            'class'          => '\munichresearch\yii2_cached_purifier\CachedPurifier',
            'cache'          => 'redis', // name of the cache component
            'cache_duration' => 0, // Duration to store the secured strings. Set it to 0 to disable expiration */
            'key_prefix'     => 'secured_strings::', // Prefix for the cache keys
            'key_hash'       => 'sha512' // Hash used to create key        
        ],
    ],
];
```

## Usage 

```php
<?= \Yii::$app->cached_purifier->purify($insecure_string) ?>
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[link-author]: https://github.com/linuskohl

