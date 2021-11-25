<?php

declare(strict_types=1);

namespace Egal\Core\Bus;

use Egal\Core\Messages\Message;

abstract class Bus
{

    abstract public function publishMessage(Message $message): void;

    abstract public function startProcessingMessages(): void;

    abstract public function stopProcessingMessages(): void;

    abstract public function processMessages(): void;

    abstract public function startConsumeReplyMessages(callable $callback): void;

    abstract public function stopConsumeReplyMessages(): void;

    abstract public function consumeReplyMessages(float $timeout = 0): void;

    public static function instance(): Bus
    {
        return app(self::class);
    }

}
