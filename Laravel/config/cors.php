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
        'http://localhost:4200',  // Student Portal
        'http://localhost:4201',  // OrgAdmin Portal
        'http://localhost:4202',  // MIS Portal
        'http://laravel-nginx-1',  // Allow requests from containers on same network
    ],

    'allowed_origins_patterns' => [
        '/^http:\/\/10\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d+$/',  // Allow all local network IPs (10.x.x.x) with any port
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
