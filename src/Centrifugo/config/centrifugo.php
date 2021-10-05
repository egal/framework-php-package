<?php

/**
 * Конфигурация Centrifugo
 */
return [
    'url' => env('CENTRIFUGO_URL', 'http://centrifugo:8000').'/api',
    'api_key' => env('CENTRIFUGO_API_KEY', 'api_key'),
    'secret' => env('CENTRIFUGO_SECRET', 'secret'),
];
