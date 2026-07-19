<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | The public JSON API (docs/API_CONTRACT.md) is consumed by apps/web on
    | a separate origin (Vercel). FRONTEND_URL (see .env.example) is a
    | comma-separated list of allowed frontend origins, set per-deployment
    | (production + preview URLs); it defaults to "*" so local dev works
    | without any env setup. We use Bearer-token auth (not cookies), so
    | credentials support stays off — no origin reflection/CSRF surface to
    | worry about.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_map('trim', explode(',', env('FRONTEND_URL', '*'))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
