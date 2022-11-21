<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeServiceMasterTokenException;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

abstract class Token
{

    protected string $typ;

    protected Carbon $exp;

    protected array $sub;

    protected string $aud;

    private const DEFAULT_TTL = 60;

    private string $signingKey;

    public function __construct()
    {
        $this->exp = Carbon::now('UTC')
            ->addSeconds(config(
                'auth.tokens.' . Str::snake(get_class_short_name(static::class)) . '.ttl',
                self::DEFAULT_TTL,
            ));
    }

    public function getSub(): array
    {
        return $this->sub;
    }

    public function setSub(array $sub): void
    {
        $this->sub = $sub;
    }

    public function getAud(): string
    {
        return $this->aud;
    }

    public function setAud(string $aud): void
    {
        $this->aud = $aud;
    }

    public function toArray(): array
    {
        return [
            'typ' => $this->typ,
            'sub' => $this->sub,
            'exp' => $this->exp->timestamp,
        ];
    }

    public static function fromArray(array $array): static
    {
        foreach (['typ', 'sub', 'exp'] as $index) {
            if (!array_key_exists($index, $array)) {
                throw new InitializeServiceMasterTokenException('Incomplete information!');
            }
        }

        $token = new static();

        if ($token->typ !== $array['typ']) {
            throw new InitializeServiceMasterTokenException('Type mismatch!');
        }

        $token->setSub((array)$array['sub']);
        $token->exp = Carbon::parse($array['exp']);

        return $token;
    }

    public static function fromJWT(string $encodedJWT, string $key): Token
    {
        return static::fromArray(static::decode($encodedJWT, $key));
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

    public function getTyp(): string
    {
        return $this->typ;
    }

    public function getExp(): Carbon
    {
        return $this->exp;
    }

    public static function decode(string $encodedJWT, string $key): array
    {
        return (array)JWT::decode($encodedJWT, $key, ['HS256']);
    }

}
