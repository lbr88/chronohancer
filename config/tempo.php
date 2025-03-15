<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tempo API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Tempo API integration.
    |
    */

    'enabled' => env('TEMPO_ENABLED', false),

    // Read-only mode - when true, allows fetching data but disables syncing
    'read_only' => env('TEMPO_READ_ONLY', false),

    // Tempo API base URL
    'base_url' => env('TEMPO_API_URL', 'https://api.tempo.io/4'),

    // OAuth 2.0 credentials
    'client_id' => env('TEMPO_CLIENT_ID', ''),
    'client_secret' => env('TEMPO_CLIENT_SECRET', ''),
    'redirect_uri' => env('TEMPO_REDIRECT_URI', ''), // The OAuth callback URL registered in Tempo settings

    // Cache settings
    'token_cache_time' => env('TEMPO_TOKEN_CACHE_TIME', 3500), // seconds (just under 1 hour)
];
