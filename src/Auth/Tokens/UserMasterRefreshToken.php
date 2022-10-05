<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeUserMasterRefreshTokenException;
use Illuminate\Support\Carbon;

class UserMasterRefreshToken extends Token
{

    protected string $type = TokenType::USER_MASTER_REFRESH;

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
            'alive_until' => $this->aliveUntil->toISOString(),
        ];
    }

    /**
     * @param string[] $array
     * @throws \Egal\Auth\Exceptions\InitializeUserMasterRefreshTokenException
     */
    public static function fromArray(array $array): Token
    {
        foreach (['type', 'auth_identification'] as $index) {
            if (!array_key_exists($index, $array)) {
                throw new InitializeUserMasterRefreshTokenException('Incomplete information!');
            }
        }
        $token = new UserMasterRefreshToken();

        if ($array['type'] !== TokenType::USER_MASTER_REFRESH) {
            throw new InitializeUserMasterRefreshTokenException('Type mismatch!');
        }

        $token->setAuthIdentification($array['auth_identification']);
        $token->aliveUntil = Carbon::parse($array['alive_until']);

        return $token;
    }

}
