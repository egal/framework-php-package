<?php

declare(strict_types=1);

namespace Egal\Core\Events;

use Laravel\Lumen\Providers\EventServiceProvider as LumenEventServiceProvider;

class EventServiceProvider extends LumenEventServiceProvider
{

    public array $globalListen = [];

    /**
     * @var array
     */
    protected $listen = [];

    public function __construct($app)
    {
        parent::__construct($app);
        EventManager::setGlobalListen($this->globalListen);
    }

}
