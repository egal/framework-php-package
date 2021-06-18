<?php

return [

    'default' => env('BUS_CONNECTION', 'rabbitmq'),

    'connections' => [

        'rabbitmq' => [

            'driver' => 'rabbitmq',

            'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,

            'host' => [
                    'host' => env('RABBITMQ_HOST', 'rabbitmq'),
                    'port' => env('RABBITMQ_PORT', 5672),
                    'user' => env('RABBITMQ_USER', 'rabbitmq'),
                    'password' => env('RABBITMQ_PASSWORD', 'rabbitmq'),
                    'vhost' => env('RABBITMQ_VHOST', '/'),
            ],

            'options' => [

                'ssl_options' => [
                    'cafile' => env('RABBITMQ_SSL_CAFILE'),
                    'local_cert' => env('RABBITMQ_SSL_LOCALCERT'),
                    'local_key' => env('RABBITMQ_SSL_LOCALKEY'),
                    'verify_peer' => env('RABBITMQ_SSL_VERIFY_PEER', true),
                    'passphrase' => env('RABBITMQ_SSL_PASSPHRASE'),
                ],

                'consume' => [
                    'sleep' => env('RABBITMQ_CONSUME_SLEEP_MILLISECONDS', 10)
                ]

            ],

        ],

    ],

];
