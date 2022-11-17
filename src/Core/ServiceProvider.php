<?php

declare(strict_types=1);

namespace Egal\Core;

use Egal\Core\Bus\Bus;
use Egal\Core\Bus\BusCreator;
use Egal\Core\Commands\EgalRunCommand;
use Egal\Core\Commands\GenerateKeyCommand;
use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EgalCoreInitializationException;
use Egal\Core\Session\Session;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{

    /**
     * Indicates if the download of the provider is pending.
     */
    protected bool $defer = true;

    /**
     * @var string[]
     */
    protected array $commands = [];

    public function register(): void
    {
        if (!($this->app instanceof Application)) {
            throw new EgalCoreInitializationException(
                'Application needs instants of ' . Application::class . ' detected ' . get_class($this->app) . '!',
            );
        }

        if ($this->app->runningInConsole()) {
            if (class_exists('Egal\CodeGenerator\ServiceProvider')) {
                $this->app->register('Egal\CodeGenerator\ServiceProvider');
            }

            if (class_exists('Egal\Validation\ServiceProvider')) {
                $this->app->register('Egal\Validation\ServiceProvider');
            }

            if (class_exists('Egal\Model\ServiceProvider')) {
                $this->app->register('Egal\Model\ServiceProvider');
            }

            $this->commands([
                EgalRunCommand::class,
                GenerateKeyCommand::class,
            ]);
        }

        $this->app->singleton(Bus::class, static fn (): Bus => BusCreator::createBus());
        $this->app->singleton(Session::class, static fn () => new Session());
        $this->app->singleton(EventManager::class, static fn () => new EventManager());

        $this->commands([]);

        $this->mergeConfigs();
    }

    private function mergeConfigs(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/app.php', 'app');
        $this->mergeConfigFrom(__DIR__ . '/config/bus.php', 'bus');
        $this->mergeConfigFrom(__DIR__ . '/config/auth.php', 'auth');
        $this->mergeConfigFrom(__DIR__ . '/config/database.php', 'database');
        $this->mergeConfigFrom(__DIR__ . '/config/queue.php', 'queue');
    }

}
