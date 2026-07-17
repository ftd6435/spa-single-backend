<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://www.spa-dev.com',
        'https://spa-dev.com',
        'http://localhost:3000',
        'http://localhost:4200',
        'http://localhost:9000',
    ],

    'allowed_origins_patterns' => [
        // Utile si vous avez des sous-domaines de preview/staging dynamiques
        // ex: https://feature-123.spa-dev.com
        '#^https://.*\.spa-dev\.com$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
