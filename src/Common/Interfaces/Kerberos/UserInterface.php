<?php

namespace EgalFramework\Common\Interfaces\Kerberos;

/**
 * Interface User
 * @package EgalFramework\Kerberos\Interfaces
 * @property string $password
 */
interface UserInterface
{

    public function getRolesArray(): array;

    public function toArray(): array;

    public function getType(): int;

}
