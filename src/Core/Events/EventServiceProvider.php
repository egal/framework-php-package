<?php /** @noinspection PhpMissingFieldTypeInspection */

namespace Egal\Core\Events;

use Laravel\Lumen\Providers\EventServiceProvider as LumenEventServiceProvider;

class EventServiceProvider extends LumenEventServiceProvider
{

    /**
     * Сопоставления обработчиков событий для всей системы.
     *
     * @var array
     */
    public array $globalListen = [];

    protected $listen = [];

    public function __construct($app)
    {
        parent::__construct($app);
        EventManager::setGlobalListen($this->globalListen);
    }

}
