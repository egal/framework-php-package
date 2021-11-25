<?php

declare(strict_types=1);

return [

    'default' => env('BUS_CONNECTION', 'rabbitmq'),

    'connections' => [

        'rabbitmq' => [

            'driver' => 'rabbitmq',

            'hosts' => [
                [
                    'host' => env('RABBITMQ_HOST', 'rabbitmq'),
                    'port' => env('RABBITMQ_PORT', 5672),
                    'user' => env('RABBITMQ_USER', 'user'),
                    'password' => env('RABBITMQ_PASSWORD'),
                ],
            ],

            'options' => [
                'ssl_options' => [
                    'cafile' => env('RABBITMQ_SSL_CAFILE'),
                    'local_cert' => env('RABBITMQ_SSL_LOCALCERT'),
                    'local_key' => env('RABBITMQ_SSL_LOCALKEY'),
                    'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
                    'passphrase' => env('RABBITMQ_SSL_PASSPHRASE'),
                ],
            ],

        ],

    ],

];
