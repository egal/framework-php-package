<?php

return [

    'default' => env('QUEUE_CONNECTION', 'rabbitmq'),

    'connections' => [

        'rabbitmq' => [

            'driver' => 'rabbitmq',
            'queue' => env('RABBITMQ_QUEUE', 'default'),
            'connection' => PhpAmqpLib\Connection\AMQPLazyConnection::class,

            'hosts' => [
                [
                    'host' => env('RABBITMQ_HOST', '127.0.0.1'),
                    'port' => env('RABBITMQ_PORT', 5672),
                    'user' => env('RABBITMQ_USER', 'guest'),
                    'password' => env('RABBITMQ_PASSWORD', 'guest'),
                    'vhost' => env('RABBITMQ_VHOST', '/'),
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
                'queue' => [
                    'job' => Egal\Core\Jobs\RabbitMQJob::class,
                    'exchange' => 'amq.topic',
                    'exchange_type' => 'topic',
                ],
            ],

            /*
             * Set to "horizon" if you wish to use Laravel Horizon.
             */
            'worker' => env('RABBITMQ_WORKER', 'default'),

        ],

    ],

    'failed' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => env('QUEUE_FAILED_TABLE', 'failed_jobs'),
    ],

];
