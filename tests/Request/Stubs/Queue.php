<?php

namespace EgalFramework\Request\Tests\Stubs;

use Closure;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\QueueInterface;
use EgalFramework\Common\Session;

class Queue implements QueueInterface
{

    public array $sent;

    public MessageInterface $newMessage;

    public string $path;

    private bool $readReturn;

    public function getPools(): array
    {
        return [];
    }

    public function deletePool(string $poolName): void
    {
    }

    public function createPool(string $poolName): void
    {
    }

    public function quit(): void
    {
    }

    public function listen(string $service, string $queue, Closure $callback, $ttl = 1): void
    {
    }

    public function getMessage(string $data): MessageInterface
    {
    }

    public function setNewMessage(MessageInterface $newMessage): void
    {
        $this->newMessage = $newMessage;
    }

    public function getNewMessageInstance(): MessageInterface
    {
        return $this->newMessage;
    }

    public function setReadReturn($readReturn): void
    {
        $this->readReturn = $readReturn;
    }

    public function read(string $service, string $queue, Closure $callback, int $ttl = 10): bool
    {
        $callback(json_encode(Session::getQueue()->getNewMessageInstance()->toArray()));
        return isset($this->readReturn)
            ? $this->readReturn
            : true;
    }

    public function send(string $service, string $queue, MessageInterface $message, int $ttl = -1): void
    {
        Session::getRegistry()->set(
            'testSentResult',
            ['service' => $service, 'queue' => $queue, 'message' => $message, 'ttl' => $ttl]
        );
    }

    public function setPath(string $name): void
    {
    }

    public function restartQueue(): void
    {
        // TODO: Implement restartQueue() method.
    }
}
