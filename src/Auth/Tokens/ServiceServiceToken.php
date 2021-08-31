<?php

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeServiceServiceTokenException;
use Illuminate\Support\Carbon;

class ServiceServiceToken extends Token
{

    protected string $type = TokenType::SERVICE_SERVICE;
    protected array $authInformation;

    /**
     * @return array
     */
    public function getAuthInformation(): array
    {
        return $this->authInformation;
    }

    /**
     * @param array $authInformation
     */
    public function setAuthInformation(array $authInformation): void
    {
        $this->authInformation = $authInformation;
    }

    public function getServiceName()
    {
        if (isset($this->authInformation['service'])) {
            return $this->authInformation['service'];
        }
        return null;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'auth_information' => $this->authInformation,
            'alive_until' => $this->aliveUntil->toISOString()
        ];
    }

    /**
     * @param array $array
     * @return ServiceServiceToken
     * @throws InitializeServiceServiceTokenException
     */
    public static function fromArray(array $array): ServiceServiceToken
    {
        foreach (['type', 'auth_information'] as $index) {
            if (!array_key_exists($index, $array)) {
                throw new InitializeServiceServiceTokenException('Incomplete information!');
            }
        }
        if (TokenType::SERVICE_SERVICE !== $array['type']) {
            throw new InitializeServiceServiceTokenException('Type mismatch!');
        }
        $token = new ServiceServiceToken();
        $token->setAuthInformation((array)$array['auth_information']);
        $token->aliveUntil = Carbon::parse($array['alive_until']);

        return $token;
    }

}
