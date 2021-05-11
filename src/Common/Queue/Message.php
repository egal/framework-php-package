<?php

namespace EgalFramework\Common\Queue;

use EgalFramework\Common\Exceptions\MessageException;
use EgalFramework\Common\Interfaces\MessageInterface;

class Message implements MessageInterface
{

    /** @var string */
    private string $uid;

    /** @var string */
    private string $model;

    /** @var string */
    private string $action;

    /** @var int */
    private int $id;

    /** @var string[] */
    private array $query;

    /** @var mixed */
    private $data;

    /** @var float */
    private float $processTime;

    /** @var string */
    private string $mandate;

    /** @var string */
    private string $hash;

    /** @var string */
    private string $clientIp;

    /** @var array */
    private array $serverInformation;

    /** @var int */
    private int $method;

    private string $sender;

    public function __construct()
    {
        $this->sender = $this->uid = $this->model = $this->action = $this->hash = $this->mandate = $this->clientIp = '';
        $this->id = $this->processTime = $this->method = 0;
        $this->query = $this->data = $this->serverInformation = [];
    }

    /**
     * @param string $data
     * @throws MessageException
     */
    public function fromJSON(string $data): void
    {
        $arr = json_decode($data, TRUE);
        if (json_last_error()) {
            throw new MessageException('Failed to parse JSON: ' . json_last_error_msg());
        }
        foreach (array_keys(get_object_vars($this)) as $field) {
            if (!empty($arr[$field])) {
                $this->{$field} = $arr[$field];
            }
        }
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        foreach (array_keys(get_object_vars($this)) as $field) {
            if (empty($this->{$field})) {
                continue;
            }
            $result[$field] = $this->{$field};
        }
        return $result;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getClientIp(): string
    {
        return $this->clientIp;
    }

    public function setClientIp(string $clientIp): void
    {
        $this->clientIp = $clientIp;
    }

    public function getServerInformation(): array
    {
        return $this->serverInformation;
    }

    public function setServerInformation(array $serverInformation): void
    {
        $this->serverInformation = $serverInformation;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function setQuery(array $query): void
    {
        $this->query = $query;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getMethod(): int
    {
        return $this->method;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function setModel(string $model): void
    {
        $this->model = $model;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setProcessTime(float $time): void
    {
        $this->processTime = $time;
    }

    public function setMethod(int $method): void
    {
        $this->method = $method;
    }

    public function getProcessTime(): float
    {
        return $this->processTime;
    }

    public function getMandate(): string
    {
        return $this->mandate;
    }

    public function setMandate(string $mandate): void
    {
        $this->mandate = $mandate;
    }

    public function setSender(string $sender): void
    {
        $this->sender = $sender;
    }

    public function getSender(): string
    {
        return $this->sender;
    }

}
