<?php

namespace EgalFramework\Kerberos;

use EgalFramework\Common\Interfaces\Kerberos\MandateInterface;

/**
 * Class KDC
 * @package EgalFramework\Kerberos
 */
class Mandate implements MandateInterface
{

    /** @var string Session key hash */
    protected string $sessionKey;

    protected MandateData $data;

    public function __construct(string $sessionKey, MandateData $data)
    {
        $this->setSessionKey($sessionKey);
        $this->setData($data);
    }

    public function setSessionKey(string $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    public function getSessionKey(): string
    {
        return $this->sessionKey;
    }

    public function setData(MandateData $data): void
    {
        $this->data = $data;
    }

    public function getData(): MandateData
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            Common::FIELD_SESSION_KEY => $this->sessionKey,
            Common::FIELD_DATA => $this->data->toArray(),
        ];
    }

}
