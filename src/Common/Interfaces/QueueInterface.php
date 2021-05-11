<?php

namespace EgalFramework\Common\Interfaces;

use Closure;

interface  QueueInterface
{

    public function getPools(): array;

    public function deletePool(string $poolName): void;

    public function createPool(string $poolName): void;

    public function quit(): void;

    public function listen(string $service, string $queue, Closure $callback, $ttl = 1): void;

    public function getMessage(string $data): MessageInterface;

    public function getNewMessageInstance(): MessageInterface;

    public function read(string $service, string $queue, Closure $callback, int $ttl = 10): bool;

    public function send(string $service, string $queue, MessageInterface $message, int $ttl = -1): void;

    public function setPath(string $name): void;

    public function restartQueue(): void;

}
