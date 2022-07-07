<?php

namespace Egal\Core\Auth;

use Egal\Core\Auth\Commands\CreateRoleCommand;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class CommandsServiceProvider extends IlluminateServiceProvider
{

    protected bool $defer = true;

    protected array $commands = [];

    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateRoleCommand::class,

            ]);
        }

        $this->commands([]);
    }

}
