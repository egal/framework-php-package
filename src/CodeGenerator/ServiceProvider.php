<?php

declare(strict_types=1);

namespace Egal\CodeGenerator;

use Egal\CodeGenerator\Commands\EventMakeCommand;
use Egal\CodeGenerator\Commands\EventServiceProviderMakeCommand;
use Egal\CodeGenerator\Commands\ListenerMakeCommand;
use Egal\CodeGenerator\Commands\MigrationCreateMakeCommand;
use Egal\CodeGenerator\Commands\MigrationDeleteMakeCommand;
use Egal\CodeGenerator\Commands\ModelMakeCommand;
use Egal\CodeGenerator\Commands\QueueConfigMakeCommand;
use Egal\CodeGenerator\Commands\RuleMakeCommand;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * Указывает, отложена ли загрузка провайдера.
     *
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
            $this->commands([
                QueueConfigMakeCommand::class,
                ModelMakeCommand::class,
                MigrationCreateMakeCommand::class,
                MigrationDeleteMakeCommand::class,
                EventMakeCommand::class,
                ListenerMakeCommand::class,
                EventServiceProviderMakeCommand::class,
                RuleMakeCommand::class,
//                \Egal\CodeGenerator\Commands\MigrationUpdateMakeCommand::class, # TODO: Восстановить.
            ]);
        }

        $this->commands([]);
    }

}
