<?php

namespace EgalFramework\Request\Tests\Stubs;

use EgalFramework\Common\Interfaces\MessageInterface;

class Message implements MessageInterface
{

    private array $fields = [];

    /** @var mixed */
    private $returnData;

    public function getUid(): string
    {
        return $this->fields['uid'];
    }

    public function setUid(string $uid): void
    {
        $this->fields['uid'] = $uid;
    }

    public function getId(): int
    {
    }

    public function setId(int $id): void
    {
    }

    public function getQuery(): array
    {
    }

    public function setQuery(array $query): void
    {
    }

    public function getModel(): string
    {
        return $this->fields['model'];
    }

    public function setModel(string $model): void
    {
        $this->fields['model'] = $model;
    }

    public function getAction(): string
    {
    }

    public function setAction(string $action): void
    {
    }

    public function getData()
    {
        return $this->returnData;
    }

    /**
     * @param mixed $returnData
     */
    public function setPrivateData($returnData): void
    {
        $this->returnData = $returnData;
    }

    public function setData($data): void
    {
    }

    public function getMethod(): int
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
    {
    }

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
        return [];
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
