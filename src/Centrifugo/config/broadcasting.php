<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'centrifugo'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'centrifugo' => [
            'driver' => 'centrifugo',
            'secret' => env('CENTRIFUGO_SECRET'),
            'api_key' => env('CENTRIFUGO_API_KEY'),
            'api_url' => env('CENTRIFUGO_API_URL', 'http://localhost:8000/api'),
            'verify' => env('CENTRIFUGO_VERIFY', false),
            'ssl_key' => env('CENTRIFUGO_SSL_KEY', null),
            'show_node_info' => env('CENTRIFUGO_SHOW_NODE_INFO', false),
        ],
    ],

];
