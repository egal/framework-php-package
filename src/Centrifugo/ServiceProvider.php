<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Egal\Centrifugo\Centrifugo as CentrifugoClient;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    public function boot(BroadcastManager $broadcastManager)
    {
        $broadcastManager->extend('centrifugo', function () {
            return new CentrifugoBroadcaster();
        });
    }

    public function register()
    {
        $this->app->singleton(CentrifugoClient::class, function ($app) {
            return new CentrifugoClient($app->make('config')->get('broadcasting.connections.centrifugo'));
        });

        $this->mergeConfigs();
    }

    private function mergeConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/centrifugo.php', 'centrifugo');

        $this->mergeConfigFrom(__DIR__ . '/config/broadcasting.php', 'broadcasting');
    }

}
