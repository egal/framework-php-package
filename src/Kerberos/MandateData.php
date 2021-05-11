<?php

namespace EgalFramework\Kerberos;

use EgalFramework\Common\Interfaces\Kerberos\MandateDataInterface;

class MandateData implements MandateDataInterface
{

    private array $user;

    private array $roles;

    public function __construct(array $user = [], array $roles = [])
    {
        $this->setUser($user);
        $this->setRoles($roles);
    }

    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    public function getUser(): array
    {
        return $this->user;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function toArray()
    {
        return [
            Common::FIELD_USER => $this->user,
            Common::FIELD_ROLES => $this->roles,
        ];
    }

    public function fromArray(array $data): void
    {
        $this->user = empty($data[Common::FIELD_USER])
            ? []
            : $data[Common::FIELD_USER];
        $this->roles = empty($data[Common::FIELD_ROLES])
            ? []
            : $data[Common::FIELD_ROLES];
    }

}
