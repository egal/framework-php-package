<?php

declare(strict_types=1);

namespace Egal\Auth\Tokens;

abstract class ServiceToken extends Token
{

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
