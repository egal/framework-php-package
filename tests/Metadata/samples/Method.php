<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;

class Method implements MethodInterface
{

    public string $name;

    public array $roles;

    public function toString(): string
    {
        return '';
    }

    public function getRoles(): array
    {
        // TODO: Implement getRoles() method.
    }
}
