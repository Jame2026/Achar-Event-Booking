<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    // Configure allowed frontend origins via env so dev/preview/prod URLs are covered.
    // Example: FRONTEND_ORIGINS=http://localhost:5173,http://127.0.0.1:4173,https://yourdomain.com
    'allowed_origins' => array_filter(explode(',', env('FRONTEND_ORIGINS', implode(',', [
        'http://127.0.0.1:5173',
        'http://localhost:5173',
        'http://127.0.0.1:4173', // Vite preview default
        'http://localhost:4173',
    ])))),
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true, // allow cookies for SPA -> API
];
