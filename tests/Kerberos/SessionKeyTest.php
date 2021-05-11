<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\KDCResponse;
use EgalFramework\Kerberos\SessionKey;
use PHPUnit\Framework\TestCase;

class SessionKeyTest extends TestCase
{

    public function testSettersGetters()
    {
        $sessionKey = new SessionKey;
        $sessionKey->setEmail('email');
        $this->assertEquals('email', $sessionKey->getEmail());
    }

    public function testEmailEmptyFromArray()
    {
        $response = new SessionKey;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['email' => '', 'data' => 'data']);
    }

    public function testEmailNotStringFromArray()
    {
        $response = new SessionKey;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['email' => [], 'data' => 'data']);
    }

    public function testDataEmptyFromArray()
    {
        $response = new SessionKey;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['email' => 'email', 'data' => '']);
    }

    public function testDataNotStringFromArray()
    {
        $response = new SessionKey;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['email' => 'email', 'data' => []]);
    }

}
