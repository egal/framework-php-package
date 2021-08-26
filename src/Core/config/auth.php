<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authorization enable
    |--------------------------------------------------------------------------
    |
    | This parameter use for disable authorization checks in current service.
    |
    */

    'enabled' => !(bool)env('AUTH_DISABLE', false),

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
            'ttl' => (int)env('AUTH_USER_MASTER_TOKEN_TTL', 86400),
        ],

        'user_service_token' => [
            'ttl' => (int)env('AUTH_USER_SERVICE_TOKEN_TTL', 600),
        ],

    ],

];
