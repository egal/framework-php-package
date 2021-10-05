<?php

namespace Egal\Centrifugo;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use phpcent\Client;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Указывает, отложена ли загрузка провайдера.
     *
     * @noinspection PhpUnusedPropertyInspection
     * @var bool
     */
    protected bool $defer = true;

    /**
     * Команды для регистрации.
     *
     * @var array
     */
    protected array $commands = [];

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }

        $this->app->singleton(Client::class, function (): Client {
            return new Client(
                config('centrifugo.url'),
                config('centrifugo.api_key'),
                config('centrifugo.secret')
            );
        });

        $this->app->singleton('events', function ($app) {
            return (new CentrifugoEventDispatcher($app));
        });

        $this->commands([]);

        $this->mergeConfigs();
    }

    private function mergeConfigs()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/centrifugo.php', 'centrifugo'
        );

    }
}
