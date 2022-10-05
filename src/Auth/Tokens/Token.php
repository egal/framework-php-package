<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

abstract class Token
{

    public const DEFAULT_TTL = 60;

    protected string $type;

    protected Carbon $aliveUntil;

    private string $signingKey;

    /**
     * @param mixed[] $array
     */
    abstract public static function fromArray(array $array): Token;

    abstract public function toArray(): array;

    public function __construct()
    {
        $this->aliveUntil = Carbon::now('UTC')
            ->addSeconds(config(
                'auth.tokens.' . Str::snake(get_class_short_name($this)) . '.ttl',
                static::DEFAULT_TTL
            ));
    }

    /**
     * @return static
     */
    public static function fromJWT(string $encodedJWT, string $key): Token
    {
        $payload = static::decode($encodedJWT, $key);

        return static::fromArray($payload);
    }

    public function isAlive(): bool
    {
        return Carbon::now('UTC') < $this->aliveUntil;
    }

    public function isAliveOrFail(): bool
    {
        if ($this->isAlive()) {
            return true;
        }

        throw TokenExpiredException::make($this->getType());
    }

    public function generateJWT(): string
    {
        return JWT::encode($this->toArray(), $this->getSigningKey());
    }

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

    public function getAliveUntil(): Carbon
    {
        return $this->aliveUntil;
    }

    /**
     * @return mixed[]
     */
    public static function decode(string $encodedJWT, string $key): array
    {
        return (array) JWT::decode($encodedJWT, $key, ['HS256']);
    }

}
