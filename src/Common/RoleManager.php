<?php

namespace EgalFramework\Common;

use EgalFramework\Common\Interfaces\RoleManagerInterface;

class RoleManager implements RoleManagerInterface
{

    private array $roles;

    public function __construct()
    {
        $this->roles = [];
    }

    public function setRole(string $name): void
    {
        $this->roles[] = $name;
        $this->setRoles($this->roles);
    }

    public function hasRole(string $name): bool
    {
        return in_array($name, $this->roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = array_unique(array_filter($roles));
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function hasRoles(array $roles): bool
    {
        return !empty(array_intersect($this->roles, $roles));
    }

}
