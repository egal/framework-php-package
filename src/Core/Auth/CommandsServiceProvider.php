<?php

namespace Egal\Core\Auth;

use Egal\Core\Auth\Commands\CreateRoleCommand;
use Egal\Core\Auth\Commands\DeleteRoleCommand;
use Egal\Core\Auth\Commands\ListRolesCommand;
use Egal\Core\Auth\Commands\SetUserRoleCommand;
use Egal\Core\Auth\Commands\UnsetUserRoleCommand;
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
                DeleteRoleCommand::class,
                ListRolesCommand::class,
                SetUserRoleCommand::class,
                UnsetUserRoleCommand::class
            ]);
        }

        $this->commands([]);
    }

}
