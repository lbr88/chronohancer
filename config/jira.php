<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Jira Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the Jira integration.
    |
    */

    'enabled' => env('JIRA_ENABLED', false),

    'base_url' => env('JIRA_BASE_URL'),

    'username' => env('JIRA_USERNAME'),

    'api_token' => env('JIRA_API_TOKEN'),

    'project_key' => env('JIRA_PROJECT_KEY'),

    // Cache duration for Jira API responses in seconds
    'cache_duration' => env('JIRA_CACHE_DURATION', 3600),
];
