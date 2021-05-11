<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\KDCResponse;
use EgalFramework\Kerberos\SessionKey;
use PHPUnit\Framework\TestCase;

class KDCResponseTest extends TestCase
{

    public function testToArray()
    {
        $response = new KDCResponse(new SessionKey('email', 'data'), 'mandate!1');
        $this->assertEquals([
            'sessionKey' => [
                'email' => 'email',
                'data' => 'data',
            ],
            'mandate' => 'mandate!1',
        ], $response->toArray());
    }

    public function testSettersGetters()
    {
        $response = new KDCResponse(new SessionKey('email', 'data'), 'mandate!1');

        $response->setSessionKey(new SessionKey('email1', 'data1'));
        $this->assertEquals(
            ['email' => 'email1', 'data' => 'data1'],
            $response->getSessionKey()->toArray()
        );

        $response->setMandate('mandate');
        $this->assertEquals('mandate', $response->getMandate());
    }

    public function testFromArray()
    {
        $response = new KDCResponse;
        $response->fromArray(['sessionKey' => ['email' => 'email', 'data' => 'data'], 'mandate' => 'mandate']);
        $this->assertEquals('email', $response->getSessionKey()->getEmail());
        $this->assertEquals('data', $response->getSessionKey()->getData());
        $this->assertEquals('mandate', $response->getMandate());
    }

    public function testSessionKeyEmptyFromArray()
    {
        $response = new KDCResponse;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['sessionKey' => [], 'mandate' => 'mandate']);
    }

    public function testSessionKeyNotArrayFromArray()
    {
        $response = new KDCResponse;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['sessionKey' => '', 'mandate' => 'mandate']);
    }

    public function testMandateEmptyFromArray()
    {
        $response = new KDCResponse;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['sessionKey' => ['email' => 'email', 'data' => 'data'], 'mandate' => '']);
    }

    public function testMandateNotStringFromArray()
    {
        $response = new KDCResponse;
        $this->expectException(IncorrectDataException::class);
        $response->fromArray(['sessionKey' => ['email' => 'email', 'data' => 'data'], 'mandate' => 123]);
    }

}
