<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | Specify the cache store that will be used by the plugin manager to
    | cache discovered plugin data.
    |
    */

    'cache_store' => env('PLUGIN_CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Plugin routes
    |--------------------------------------------------------------------------
    |
    | Specify rules for route files look up. Provide route filename patterns
    | and route middleware.
    |
    */

    'routes' => [
        'web' => [
            'filename' => 'web.php',
            'middleware' => 'web'
        ],
        'api' => [
            'filename' => 'api*.php',
            'middleware' => 'api'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin broadcast channels
    |--------------------------------------------------------------------------
    |
    | Here we can configure broadcast authorization logic files. It's possible
    | to use '*' placeholders in the filenames.
    |
    */

    'channels' => [
        'channels.php'
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugin groups
    |--------------------------------------------------------------------------
    |
    | Here you may define all the plugin groups necessary for your application
    | specifying directories where modules are going to be located. Path should
    | be specified relative to project root directory (base_path()).
    |
    | Examples:
    |   "modules" -> "path: modules" -> "<root>/modules"
    |   "widgets" -> "path: widgets" -> "<root>/widgets"
    |
    */

    'groups' => [
        'modules' => [
            'path' => 'modules',
            // 'routes' => []
            // 'channels' => []
        ]
    ]
];
