<?php

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeUserMasterTokenException;
use Illuminate\Support\Carbon;

class UserMasterToken extends Token
{

    protected string $type = TokenType::USER_MASTER;

    /**
     * @var string|int
     */
    private $authIdentification;

    /**
     * @return int|string
     */
    public function getAuthIdentification()
    {
        return $this->authIdentification;
    }

    /**
     * @param int|string $authIdentification
     */
    public function setAuthIdentification($authIdentification): void
    {
        $this->authIdentification = $authIdentification;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'auth_identification' => $this->authIdentification,
            'alive_until' => $this->aliveUntil->toISOString()
        ];
    }

    /**
     * @throws InitializeUserMasterTokenException
     */
    public static function fromArray(array $array): Token
    {
        foreach (
            ['type', 'auth_identification'] as $index
        ) {
            if (!array_key_exists($index, $array)) {
                throw new InitializeUserMasterTokenException('Incomplete information!');
            }
        }
        $token = new UserMasterToken();
        if (TokenType::USER_MASTER !== $array['type']) {
            throw new InitializeUserMasterTokenException('Type mismatch!');
        }
        $token->setAuthIdentification($array['auth_identification']);
        $token->aliveUntil = Carbon::parse($array['alive_until']);
        return $token;
    }

}
