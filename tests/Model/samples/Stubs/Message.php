<?php

namespace EgalFramework\Model\Tests\Samples\Stubs;

use EgalFramework\Common\Interfaces\MessageInterface;

class Message implements MessageInterface
{

    private int $id;

    private array $query;

    public function __construct()
    {
        $this->query = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    public function getModel(): string
    {
    }

    public function getAction(): string
    {
    }

    public function getData()
    {
    }

    public function getMethod(): int
    {
    }

    public function getUid(): string
    {
    }

    public function setUid(string $uid): void
    {
    }

    public function setModel(string $model): void
    {
    }

    public function setAction(string $action): void
    {
    }

    public function setData($data): void
    {
    }

    public function setMethod(int $method): void
    {
    }

    public function getHash(): string
    {
    }

    public function setHash(string $hash): void
    {
    }

    public function getProcessTime(): float
    {
    }

    public function setProcessTime(float $time): void
    {}

    public function getMandate(): string
    {
    }

    public function setMandate(string $mandate): void
    {
    }

    public function fromJSON(string $data): void
    {
    }

    public function toArray(): array
    {
    }

    public function getClientIp(): string
    {
        // TODO: Implement getClientIp() method.
    }

    public function setClientIp(string $clientIp): void
    {
        // TODO: Implement setClientIp() method.
    }

    public function getServerInformation(): array
    {
        // TODO: Implement getServerInformation() method.
    }

    public function setServerInformation(array $serverInformation): void
    {
        // TODO: Implement setServerInformation() method.
    }

    public function setSender(string $sender): void
    {
        // TODO: Implement setSender() method.
    }

    public function getSender(): string
    {
        // TODO: Implement getSender() method.
    }
}
