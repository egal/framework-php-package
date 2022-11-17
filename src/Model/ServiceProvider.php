<?php

declare(strict_types=1);

namespace Egal\Model;

use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

/**
 * @package Egal\Model
 */
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

        $this->app->singleton('ModelMetadataManager', fn () => new ModelMetadataManager());

        $this->commands([]);
    }

}
