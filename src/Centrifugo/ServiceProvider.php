<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Egal\Centrifugo\Centrifugo as CentrifugoClient;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    public function boot(BroadcastManager $broadcastManager): void
    {
        $broadcastManager->extend(
            'centrifugo',
            static fn () => new CentrifugoBroadcaster()
        );
    }

    public function register(): void
    {
        $this->app->singleton(
            CentrifugoClient::class,
            static fn ($app) => new CentrifugoClient($app->make('config')->get('broadcasting.connections.centrifugo'))
        );

        $this->mergeConfigs();
    }

    private function mergeConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/broadcasting.php', 'broadcasting');
    }

}
