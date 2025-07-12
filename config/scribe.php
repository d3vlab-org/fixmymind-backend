<?php

return [

    'title'       => config('app.name').' API Documentation',
    'description' => 'API terapeutyczne do rozmów z AI',
    'base_url'    => config('app.url'),

    // Przywróć tę sekcję, żeby Scribe wiedział co i jak skanować:
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains'  => ['*'],
            ],
            'include' => [],  // puste = wszystkie api/* 
            'exclude' => [],
        ],
    ],

    'type' => 'laravel',

    'auth' => [
        'enabled'     => true,
        'default'     => true,
        'in'          => 'header',
        'name'        => 'Authorization',
        'use_value'   => 'Basic '.base64_encode(env('API_DOC_USER', 'user').':'.env('API_DOC_PASSWORD', 'pass')),
        'placeholder' => 'Basic {credentials}',
        'extra_info'  => 'Use Basic Auth with your credentials (username:password encoded in Base64).',
    ],

    'example_languages' => ['bash', 'javascript'],

    'openapi' => [
        'enabled' => true,
    ],

    'postman' => [
        'enabled' => true,  // <-- usuń literówkę tu
    ],

    'try_it_out' => [
        'enabled'   => true,
        'base_url'  => null,
        'use_csrf'  => false,
        'csrf_url'  => '/sanctum/csrf-cookie',
    ],
];