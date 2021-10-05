<?php

namespace Egal\Core;

use Egal\Centrifugo\CentrifugoEventDispatcher;
use Egal\Core\Bus\Bus;
use Egal\Core\Bus\BusCreator;
use Egal\Core\Commands\EgalListenerRunCommand;
use Egal\Core\Commands\EgalRunCommand;
use Egal\Core\Commands\GenerateKeyCommand;
use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EgalCoreInitializationException;
use Egal\Core\Session\Session;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use phpcent\Client;
use VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider;

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

    /**
     * @throws EgalCoreInitializationException
     */
    public function register(): void
    {
        if (!($this->app instanceof Application)) {
            throw new EgalCoreInitializationException(
                'Application needs instants of ' . Application::class . ' detected ' . get_class($this->app) . '!'
            );
        }

        $this->app->register(LaravelQueueRabbitMQServiceProvider::class);

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
                EgalListenerRunCommand::class,
                GenerateKeyCommand::class,
            ]);
        }

        $this->app->singleton(Bus::class, function (): Bus {
            return BusCreator::createBus();
        });

        $this->app->singleton(Session::class, function () {
            return new Session();
        });

        $this->app->singleton(EventManager::class, function () {
            return new EventManager();
        });

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
            __DIR__ . '/config/app.php', 'app'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/auth.php', 'auth'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/database.php', 'database'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/config/queue.php', 'queue'
        );
    }

}
