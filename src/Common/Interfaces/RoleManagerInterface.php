<?php

namespace EgalFramework\Common\Interfaces;

interface RoleManagerInterface
{

    public function __construct();

    public function setRole(string $name): void;

    public function hasRole(string $name): bool;

    public function setRoles(array $roles): void;

    public function getRoles(): array;

    public function hasRoles(array $roles): bool;

}
