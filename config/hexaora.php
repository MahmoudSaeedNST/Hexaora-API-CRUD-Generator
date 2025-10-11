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
];
