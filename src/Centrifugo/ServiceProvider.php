<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use phpcent\Client;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * Указывает, отложена ли загрузка провайдера.
     *
     * @noinspection PhpUnusedPropertyInspection
     */
    protected bool $defer = true;

    /**
     * Команды для регистрации.
     *
     * @var string[]
     */
    protected array $commands = [];

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }

        $this->app->singleton(
            Client::class,
            static fn (): Client => new Client(
                config('centrifugo.url'),
                config('centrifugo.api_key'),
                config('centrifugo.secret')
            )
        );

        $this->app->singleton(
            'events',
            static fn ($app) => new CentrifugoEventDispatcher($app)
        );

        $this->commands([]);

        $this->mergeConfigs();
    }

    private function mergeConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/centrifugo.php', 'centrifugo');
    }

}
