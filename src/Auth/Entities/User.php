<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Tokens\UserServiceToken;

class User extends Client
{
    public readonly string $key;

    public readonly array $roles;

    public readonly array $permissions;

    public readonly array $authInformation;

    public function __construct(UserServiceToken $ust)
    {
        $this->key = $ust->getUid();
        $this->roles = $ust->getRoles();
        $this->permissions = $ust->getPermissions();
        $this->authInformation = $ust->getAuthInformation();
    }

}
