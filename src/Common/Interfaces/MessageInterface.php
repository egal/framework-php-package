<?php

namespace EgalFramework\Common\Interfaces;

interface MessageInterface
{

    public function getUid(): string;

    public function setUid(string $uid): void;

    public function getId(): int;

    public function setId(int $id): void;

    public function getClientIp(): string;

    public function setClientIp(string $clientIp): void;

    public function getServerInformation(): array;

    public function setServerInformation(array $serverInformation): void;

    public function getQuery(): array;

    public function setQuery(array $query): void;

    public function getModel(): string;

    public function setModel(string $model): void;

    public function getAction(): string;

    public function setAction(string $action): void;

    /**
     * @return mixed
     */
    public function getData();

    /**
     * @param mixed $data
     */
    public function setData($data): void;

    public function getMethod(): int;

    public function setMethod(int $method): void;

    public function getHash(): string;

    public function setHash(string $hash): void;

    public function getProcessTime(): float;

    public function setProcessTime(float $time): void;

    public function getMandate(): string;

    public function setMandate(string $mandate): void;

    public function setSender(string $sender): void;

    public function getSender(): string;

    public function fromJSON(string $data): void;

    public function toArray(): array;

}
