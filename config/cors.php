<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
        'graphql',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://vuqia.net',
        'http://localhost:5173',
        'https://vuqia.netlify.app',
        'https://vuqia.softvencealpha.com'
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];