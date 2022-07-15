<?php

namespace Egal\Core\Auth;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

abstract class Token
{

    //TODO задать дефолтные ttl для каждого типа токена и добавить их в документацию
    public const DEFAULT_TTL = 60;

    protected TokenType $type;

    protected Carbon $exp;

    public function __construct()
    {
        $this->exp = Carbon::now('UTC')
            ->addSeconds(config('auth.tokens.' . Str::snake(class_basename(static::class)) . '.ttl', static::DEFAULT_TTL));
    }

    /**
     * @param mixed[] $array
     */
    abstract public static function fromArray(array $array): Token;

    abstract public function toArray(): array;

    /**
     * @return static
     */
    public static function fromJWT(string $encodedJWT): Token
    {
        $payload = (array) JWT::decode($encodedJWT, new Key(config('auth.public_key'), 'RS256'));

        return static::fromArray($payload);
    }

    public function isAlive(): bool
    {
        return Carbon::now('UTC') < $this->exp;
    }

    public function isAliveOrFail(): bool
    {
        if ($this->isAlive()) {
            return true;
        }

        // TODO отдельный exception
        throw new Exception('Token expire!', Response::HTTP_BAD_REQUEST);
    }

    public function generateJWT(): string
    {
        return JWT::encode($this->toArray(), config('auth.private_key'), 'RS256');
    }

    public function getType(): string
    {
        return $this->type->value;
    }

    public function getExp(): Carbon
    {
        return $this->exp;
    }

}
