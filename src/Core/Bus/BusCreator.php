<?php

declare(strict_types=1);

namespace Egal\Core\Bus;

use Egal\Core\Exceptions\BusCreatorException;
use Illuminate\Support\Arr;

class BusCreator
{

    public static function createBus(): Bus
    {
        $connectionConfig = config('bus.connections.' . config('bus.default'));

        if ($connectionConfig === null) {
            throw new BusCreatorException('Bus connection not provided!');
        }

        switch (Arr::get($connectionConfig, 'driver')) {
            case 'rabbitmq':
                return new RabbitMQBus($connectionConfig);
            case null:
                throw new BusCreatorException('Bus connection driver not provided!');
            default:
                throw new BusCreatorException('Unsupported queue driver type!');
        }
    }

}
