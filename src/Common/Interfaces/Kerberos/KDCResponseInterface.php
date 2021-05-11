<?php

namespace EgalFramework\Common\Interfaces\Kerberos;

interface KDCResponseInterface
{

    public function fromArray(array $data): void;

    public function getMandate(): string;

}
