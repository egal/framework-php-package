<?php

namespace Egal\Auth\Tokens;

use Egal\Exception\TokenExpiredAuthException;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

abstract class Token
{

    const DEFAULT_TTL = 60;

    protected string $type;
    private string $signingKey;
    protected Carbon $aliveUntil;

    #region abstract methods

    abstract public function toArray(): array;

    abstract public static function fromArray(array $array): Token;

    #endregion abstract methods

    public function __construct()
    {
        $this->aliveUntil = Carbon::now('UTC')
            ->addSeconds(config(
                'auth.tokens.' . Str::snake(get_class_short_name($this)) . '.ttl',
                static::DEFAULT_TTL
            ));
    }

    public function isAlive(): bool
    {
        return Carbon::now('UTC') < $this->aliveUntil;
    }

    /**
     * @return bool
     * @throws TokenExpiredAuthException
     */
    public function isAliveOrFail(): bool
    {
        if ($this->isAlive()) {
            return true;
        } else {
            throw new TokenExpiredAuthException();
        }
    }

    public function generateJWT(): string
    {
        return JWT::encode($this->toArray(), $this->getSigningKey());
    }

    /**
     * @param string $encodedJWT
     * @param string $key
     * @return static
     */
    public static function fromJWT(string $encodedJWT, string $key): Token
    {
        $payload = (array)JWT::decode($encodedJWT, $key, ['HS256']);
        return static::fromArray($payload);
    }

    #region getters and setters

    public function getSigningKey(): string
    {
        return $this->signingKey;
    }

    public function setSigningKey(string $signingKey): void
    {
        $this->signingKey = $signingKey;
    }

    public function getType(): string
    {
        return $this->type;
    }

    #endregion getters and setters

    /**
     * @return Carbon
     */
    public function getAliveUntil(): Carbon
    {
        return $this->aliveUntil;
    }

}
