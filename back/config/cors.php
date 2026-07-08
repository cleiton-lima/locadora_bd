<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Autenticação é via JWT (Authorization: Bearer), sem cookies — por isso
    | supports_credentials fica false e allowed_origins é restrito ao front
    | configurado em FRONTEND_URL, em vez de aberto para '*'.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
