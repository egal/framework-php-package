<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\API;
use PHPUnit\Framework\TestCase;

/**
 * Class APITestTest
 */
class ClientAPITest extends TestCase
{

    public function testClientEncrypt()
    {
        $userKey = '123321';
        $email = 'test@unit.test';
        $result = (new API)->getClientRequest($email, $userKey);

        $this->assertEquals($email, $result->getEmail());
        $clientData = json_decode($result->getData(), true);
        $this->assertNotEquals($userKey, $clientData['data']);
        $this->assertNotEmpty($clientData['initVector']);
    }

}
