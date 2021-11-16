<?php

declare(strict_types=1);

namespace Egal\Core\Bus;

use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\Message;

abstract class Bus
{

    abstract public function publishMessage(Message $message): void;

    abstract public function startProcessingMessages(): void;

    abstract public function stopProcessingMessages(): void;

    abstract public function processMessages(): void;

    abstract public function startConsumeReplyMessages(ActionMessage $actionMessage, callable $callback): void;

    abstract public function stopConsumeReplyMessages(ActionMessage $actionMessage): void;

    abstract public function consumeReplyMessages(float $timeout = 0): void;

    public static function getInstance(): Bus
    {
        return app(self::class);
    }

}
