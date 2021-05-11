<?php

namespace Egal\Core\Bus;

use Egal\Core\Messages\Message;

abstract class Bus
{

    public static function getInstance(): Bus
    {
        return app(Bus::class);
    }

    abstract public function publishMessage(Message $message): void;

    abstract public function constructEnvironment(): void;

    abstract public function destructEnvironment(): void;

}
