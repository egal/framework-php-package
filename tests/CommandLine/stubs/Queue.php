<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use Closure;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\QueueInterface;

class Queue implements QueueInterface
{

    public function getPools(): array
    {
        return ['qqq'];
    }

    public function deletePool(string $poolName): void
    {
        // TODO: Implement deletePool() method.
    }

    public function createPool(string $poolName): void
    {
        // TODO: Implement createPool() method.
    }

    public function quit(): void
    {
        // TODO: Implement quit() method.
    }

    public function listen(string $service, string $queue, Closure $callback, $ttl = 1): void
    {
        // TODO: Implement listen() method.
    }

    public function getMessage(string $data): MessageInterface
    {
        // TODO: Implement getMessage() method.
    }

    public function getNewMessageInstance(): MessageInterface
    {
        // TODO: Implement getNewMessageInstance() method.
    }

    public function send(string $service, string $queue, MessageInterface $message, int $ttl = -1): void
    {
        // TODO: Implement send() method.
    }

    public function read(string $service, string $queue, Closure $callback, int $ttl = 10): bool
    {
        // TODO: Implement read() method.
    }

    public function setPath(string $name): void
    {
        // TODO: Implement setPath() method.
    }

    public function restartQueue(): void
    {
        // TODO: Implement restartQueue() method.
    }
}
