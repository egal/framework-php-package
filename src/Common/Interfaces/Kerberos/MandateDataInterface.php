<?php

namespace EgalFramework\Common\Interfaces\Kerberos;

interface MandateDataInterface
{

    public function getUser(): array;

    public function getRoles(): array;

}
