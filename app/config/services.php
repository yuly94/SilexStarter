<?php

return [
    'common' => [
        'Silex\Provider\SessionServiceProvider',
        'Silex\Provider\ServiceControllerServiceProvider',
        'Silex\Provider\UrlGeneratorServiceProvider',
        'SilexStarter\Provider\ModuleServiceProvider',
        'SilexStarter\Provider\ResponseBuilderServiceProvider',
        'SilexStarter\Provider\RouteBuilderServiceProvider',
        'SilexStarter\Provider\EloquentServiceProvider',
        'SilexStarter\Provider\SentryServiceProvider',
        'SilexStarter\Provider\FilesystemServiceProvider',
        'SilexStarter\Provider\AssetManagerServiceProvider',
        'SilexStarter\Provider\StaticProxyServiceProvider',
        'SilexStarter\Provider\TwigServiceProvider',
        'SilexStarter\Provider\MenuManagerServiceProvider',
        'SilexStarter\Provider\MigrationServiceProvider',
    ],

    'web' => [
    ],

    'web_dev' => [
        'SilexStarter\Provider\WebProfilerServiceProvider' => ['profiler.cache_dir' => APP_PATH.'storage/profiler'],
        'Whoops\Provider\Silex\WhoopsServiceProvider'
    ],

    'console'   => [
        'SilexStarter\Provider\ConsoleServiceProvider',
    ],

    'console_dev' => [

    ]
];
