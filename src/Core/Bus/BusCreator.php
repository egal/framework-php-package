<?php

namespace Egal\Core\Bus;

use Egal\Core\Exceptions\BusCreatorException;
use Exception;

class BusCreator
{

    /**
     * @return Bus
     * @throws Exception
     */
    public static function createBus(): Bus
    {
        $defaultConnection = config('queue.default');
        $driver = config("queue.connections.$defaultConnection.driver");
        switch ($driver) {
            case 'rabbitmq':
                return new RabbitMQBus();
            default:
                throw new BusCreatorException("Unsupported queue driver type - $driver!");
        }
    }

}
