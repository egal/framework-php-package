<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization enable
    |--------------------------------------------------------------------------
    |
    | This parameter use for disable authorization checks in current service.
    |
    */

    'enabled' => !(bool) env('AUTH_DISABLE', false),

    /*
    |--------------------------------------------------------------------------
    | Authorization tokens
    |--------------------------------------------------------------------------
    |
    | Configuration of authorization tokens.
    |
    */

    'tokens' => [

        'user_master_token' => [
            'ttl' => (int) env('AUTH_USER_MASTER_TOKEN_TTL', 86400),
        ],

        'user_master_refresh_token' => [
            'ttl' => (int) env('AUTH_USER_MASTER_REFRESH_TOKEN_TTL', 604800),
        ],

        'user_service_token' => [
            'ttl' => (int) env('AUTH_USER_SERVICE_TOKEN_TTL', 600),
        ],

    ],

    'auth_service_name' => env('AUTH_SERVICE_NAME', 'auth'),

];
