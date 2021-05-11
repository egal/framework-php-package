<?php

namespace EgalFramework\Common\Interfaces\Kerberos;

interface KerberosInterface
{

    public function getNewKDCResponse(): KDCResponseInterface;

    public function getMandate(string $encryptedData, string $password): MandateInterface;

}
