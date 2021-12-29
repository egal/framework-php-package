<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeServiceServiceTokenException;
use Illuminate\Support\Carbon;

class ServiceServiceToken extends Token
{

    protected string $type = TokenType::SERVICE_SERVICE;

    /**
     * @var mixed[]
     */
    protected array $authInformation;

    /**
     * @param array $array
     * @throws \Egal\Auth\Exceptions\InitializeServiceServiceTokenException
     */
    public static function fromArray(array $array): ServiceServiceToken
    {
        foreach (['type', 'auth_information'] as $index) {
            if (!array_key_exists($index, $array)) {
                throw new InitializeServiceServiceTokenException('Incomplete token information!');
            }
        }

        if ($array['type'] !== TokenType::SERVICE_SERVICE) {
            throw new InitializeServiceServiceTokenException('Token type mismatch!');
        }

        $token = new ServiceServiceToken();
        $token->setAuthInformation((array) $array['auth_information']);
        $token->aliveUntil = Carbon::parse($array['alive_until']);

        return $token;
    }

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

    public function getServiceName(): ?string
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
            'alive_until' => $this->aliveUntil->toISOString(),
        ];
    }

}
