<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Tokens\UserServiceToken;

class User extends Client
{

    public readonly array $roles;

    public readonly array $permissions;

    public readonly array $sub;

    public function __construct(UserServiceToken $ust)
    {
        $this->roles = $ust->getRoles();
        $this->permissions = $ust->getPermissions();
        $this->sub = $ust->getSub();
    }

}
