<?php

namespace Egal\Core\Auth;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class AccessToken extends Token
{
    protected TokenType $type = TokenType::Access;
    protected string $sub;
    protected array $roles;

    public static function fromArray(array $array): Token
    {
        if (!in_array(['type', 'exp', 'sub', 'roles'], array_keys($array))) {
            // TODO отдельный exception
            throw new Exception('Incomplete token information!', Response::HTTP_BAD_REQUEST);
        }

        $token = new self();
        if (TokenType::Access !== $array['type']) {
            // TODO отдельный exception
            throw new Exception('Token type mismatch!', Response::HTTP_BAD_REQUEST);
        }
        $token->sub = $array['sub'];
        $token->roles = $array['roles'];

        return $token;
    }

    public static function fromUser(User $user): self
    {
        $token = new self();

        $token->sub = $user->getAttribute($user->getKeyName());
        $token->roles = $user->roles->pluck('name')->toArray();

        return $token;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'exp' => $this->exp,
            'sub' => $this->sub,
            'roles' => $this->roles,
        ];
    }

    public function getSub()
    {
        return $this->sub;
    }

    public function setSub($sub): void
    {
        $this->sub = $sub;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
}
