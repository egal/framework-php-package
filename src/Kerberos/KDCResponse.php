<?php

namespace EgalFramework\Kerberos;

use EgalFramework\Common\Interfaces\Kerberos\KDCResponseInterface;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;

/**
 * Class KDC
 * @package EgalFramework\Kerberos
 */
class KDCResponse implements KDCResponseInterface
{

    protected ?SessionKey $sessionKey;

    /** @var string Encrypted by server key mandate */
    protected string $mandate;

    public function __construct(SessionKey $sessionKey = null, string $mandate = '')
    {
        $this->setSessionKey($sessionKey);
        $this->setMandate($mandate);
    }

    public function setSessionKey(?SessionKey $sessionKey): void
    {
        $this->sessionKey = $sessionKey;
    }

    public function getSessionKey(): SessionKey
    {
        return $this->sessionKey;
    }

    public function setMandate(string $mandate): void
    {
        $this->mandate = $mandate;
    }

    public function getMandate(): string
    {
        return $this->mandate;
    }

    public function toArray(): array
    {
        return [
            Common::FIELD_SESSION_KEY => $this->sessionKey->toArray(),
            Common::FIELD_MANDATE => $this->mandate,
        ];
    }

    /**
     * @param array $data
     * @throws IncorrectDataException
     */
    public function fromArray(array $data): void
    {
        if (empty($data[Common::FIELD_SESSION_KEY]) || !is_array($data[Common::FIELD_SESSION_KEY])) {
            throw new IncorrectDataException('Incorrect session key specified', 401);
        }
        if (empty($data[Common::FIELD_MANDATE]) || !is_string($data[Common::FIELD_MANDATE])) {
            throw new IncorrectDataException('Incorrect mandate specified', 401);
        }
        $this->setSessionKey(new SessionKey);
        $this->sessionKey->fromArray($data[Common::FIELD_SESSION_KEY]);
        $this->setMandate($data[Common::FIELD_MANDATE]);
    }

}
