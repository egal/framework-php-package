<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\MandateData;
use PHPUnit\Framework\TestCase;

class MandateDataTest extends TestCase
{

    public function testSettersGetters()
    {
        $mandateData = new MandateData;
        $mandateData->setRoles(['role1', 'role2']);
        $this->assertEquals(['role1', 'role2'], $mandateData->getRoles());
        $mandateData->setUser(['field' => 'value']);
        $this->assertEquals(['field' => 'value'], $mandateData->getUser());
    }

    public function testFromArray()
    {
        $mandateData = new MandateData;
        $mandateData->fromArray([
            'user' => ['userData'],
            'roles' => ['rolesData'],
        ]);
        $this->assertEquals(
            [
                'user' => ['userData'],
                'roles' => ['rolesData'],
            ], $mandateData->toArray()
        );
    }

}
