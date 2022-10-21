<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

use Egal\Auth\Exceptions\InitializeUserServiceTokenException;
use Egal\Auth\Exceptions\UserServiceTokenException;
use Illuminate\Support\Carbon;

class UserServiceToken extends Token
{

    protected string $typ = TokenType::USER_SERVICE;

    public function getRoles(): array
    {
        return array_key_exists('roles', $this->sub)
            ? $this->sub['roles']
            : [];
    }

    public function getPermissions(): array
    {
        return array_key_exists('permissions', $this->sub)
            ? $this->sub['permissions']
            : [];
    }

}
