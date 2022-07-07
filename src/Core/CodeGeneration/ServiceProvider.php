<?php

namespace Egal\Core\CodeGeneration;

use Egal\Core\CodeGeneration\Commands\MakeModelCommand;
use Egal\Core\CodeGeneration\Commands\MakePolicyCommand;
use Egal\Core\CodeGeneration\Commands\MakeRouteCommand;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    protected bool $defer = true;

    protected array $commands = [];

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeModelCommand::class,
                MakePolicyCommand::class,
                MakeRouteCommand::class
            ]);
        }

        $this->commands([]);
    }

}
