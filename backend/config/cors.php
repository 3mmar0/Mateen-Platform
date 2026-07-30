<?php

$origins = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('CORS_ALLOWED_ORIGINS', env('FRONTEND_URL', 'http://localhost:3000')))
)));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => $origins,
    'allowed_origins_patterns' => [
        '#^https://.*\\.github\\.io$#',
        '#^https://mateen\\.ammarelgndy\\.cloud$#',
        '#^https://mateen\\.academy$#',
        '#^https://.*\\.mateen\\.academy$#',
        '#^http://187\\.127\\.71\\.130$#',
        '#^https://187\\.127\\.71\\.130$#',
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
