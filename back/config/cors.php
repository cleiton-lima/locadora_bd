<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Autenticação é via JWT (Authorization: Bearer), sem cookies — por isso
    | supports_credentials fica false. Em produção, restrinja via FRONTEND_URLS
    | ou FRONTEND_URL; em desenvolvimento, o fallback libera origens comuns
    | do Vite local.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_values(array_unique(array_filter(array_map(
        'trim',
        explode(',', env(
            'FRONTEND_URLS',
            env('FRONTEND_URL', 'http://localhost:5173').',http://127.0.0.1:5173,http://0.0.0.0:5173'
        ))
    )))),

    'allowed_origins_patterns' => array_values(array_unique(array_filter(array_map(
        'trim',
        explode(',', env(
            'FRONTEND_ORIGIN_PATTERNS',
            '#^http://(10|172\.(1[6-9]|2[0-9]|3[0-1])|192\.168)\.[0-9.]+:5173$#'
        ))
    )))),

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
