<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Module Namespace
    |--------------------------------------------------------------------------
    |
    | This value determines the default namespace for generated modules.
    | You can change this if you want to use a different base namespace.
    |
    */
    'module_namespace' => 'App\\Modules',

    /*
    |--------------------------------------------------------------------------
    | Default Pagination
    |--------------------------------------------------------------------------
    |
    | The default number of items per page for paginated responses.
    |
    */
    'pagination' => [
        'per_page' => 15,
    ],

    /*
    |--------------------------------------------------------------------------
    | Stub Path
    |--------------------------------------------------------------------------
    |
    | The path where stub files are located.
    |
    */
    'stub_path' => base_path('stubs/hexaora'),

    /*
    |--------------------------------------------------------------------------
    | Policy Configuration
    |--------------------------------------------------------------------------
    |
    | Configure policy generation behavior.
    | - spatie: Enable Spatie permission integration (requires spatie/laravel-permission)
    | - namespace: Namespace pattern for policies ({module} will be replaced)
    | - auto_register: Automatically register policies in AuthServiceProvider
    |
    */
    'policies' => [
        'spatie' => false,
        'namespace' => 'App\\Modules\\{module}\\Domain\\Policies',
        'auto_register' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Factory Configuration
    |--------------------------------------------------------------------------
    |
    | Configure factory generation behavior.
    | - count: Default number of records to create in seeders
    | - namespace: Namespace pattern for factories ({module} will be replaced)
    |
    */
    'factories' => [
        'count' => 10,
        'namespace' => 'App\\Modules\\{module}\\Database\\Factories',
    ],

    /*
    |--------------------------------------------------------------------------
    | Seeder Configuration
    |--------------------------------------------------------------------------
    |
    | Configure seeder generation behavior.
    | - namespace: Namespace pattern for seeders ({module} will be replaced)
    |
    */
    'seeders' => [
        'namespace' => 'App\\Modules\\{module}\\Database\\Seeders',
    ],
];
