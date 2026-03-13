<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'api/documentation'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:5173',
        'http://localhost:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'Content-Disposition'
    ],

    'max_age' => 0,

    'supports_credentials' => true,
];
