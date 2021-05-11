<?php

namespace EgalFramework\Kerberos;

use EgalFramework\Kerberos\Exceptions\IncorrectDataException;

/**
 * Class Client
 * @package EgalFramework\Kerberos
 */
class ClientRequest
{

    protected string $email;

    /** @var string Encrypted timestamp */
    protected string $data;

    public function __construct(string $email = '', string $data = '')
    {
        $this->email = $email;
        $this->data = $data;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            Common::FIELD_EMAIL => $this->email,
            Common::FIELD_DATA => $this->data,
        ];
    }

    /**
     * @param array $data
     * @throws IncorrectDataException
     */
    public function fromArray(array $data): void
    {
        if (empty($data[Common::FIELD_EMAIL]) || !is_string($data[Common::FIELD_EMAIL])) {
            throw new IncorrectDataException('Email is not specified', 401);
        }
        if (empty($data[Common::FIELD_DATA]) || !is_string($data[Common::FIELD_DATA])) {
            throw new IncorrectDataException('Data is not specified', 401);
        }
        $this->setEmail($data[Common::FIELD_EMAIL]);
        $this->setData($data[Common::FIELD_DATA]);
    }

}
