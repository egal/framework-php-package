<?php

namespace EgalFramework\Kerberos\Tests\Samples;

use EgalFramework\Common\Interfaces\Kerberos\UserInterface;

class User implements UserInterface
{

    /** @var string @TODO remove password */
    public string $password;

    public function __construct(string $password)
    {
        $this->password = $password;
    }

    public function getRolesArray(): array
    {
        return [];
    }

    public function toArray(): array
    {
        return [];
    }

    public function getType(): int
    {
    }

}
