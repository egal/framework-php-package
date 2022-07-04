<?php

namespace Egal\Core\Console;

use Egal\Core\Console\Commands\MakeModelCommand;
use Egal\Core\Console\Commands\MakePolicyCommand;
use Egal\Core\Console\Commands\MakeRouteCommand;
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
