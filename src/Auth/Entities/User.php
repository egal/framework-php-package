<?php

declare(strict_types=1);

namespace Egal\Auth\Entities;

use Egal\Auth\Tokens\ServiceToken;

class User extends Client
{

    protected readonly array $roles;

    protected readonly array $permissions;

    protected readonly array $sub;

    public function __construct(ServiceToken $ust)
    {
        $this->roles = $ust->getRoles();
        $this->permissions = $ust->getPermissions();
        $this->sub = $ust->getSub();
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * @param string[] $roles
     */
    public function hasRoles(array $roles): bool
    {
        return !array_diff($roles, $this->getRoles());
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getSub(): array
    {
        return $this->sub;
    }

}
