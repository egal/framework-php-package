<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\ClientRequest;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use PHPUnit\Framework\TestCase;

class ClientRequestTest extends TestCase
{

    public function testToArray()
    {
        $clientRequest = new ClientRequest('email@mail.ru', 'dataString');
        $this->assertEquals(['email' => 'email@mail.ru', 'data' => 'dataString'], $clientRequest->toArray());
    }

    /**
     * @throws IncorrectDataException
     */
    public function testFromArray()
    {
        $arr = ['email' => 'email@mail.ru', 'data' => 'dataString'];
        $clientRequest = new ClientRequest;
        $clientRequest->fromArray($arr);
        $this->assertEquals($arr, $clientRequest->toArray());
    }

    public function testEmptyData()
    {
        $this->expectException(IncorrectDataException::class);
        $clientRequest = new ClientRequest;
        $clientRequest->fromArray(['email' => 'email@mail.ru', 'data' => '']);
    }

    public function testWrongData()
    {
        $this->expectException(IncorrectDataException::class);
        $clientRequest = new ClientRequest;
        $clientRequest->fromArray(['email' => 'email@mail.ru', 'data' => 123]);
    }

    public function testEmptyEmail()
    {
        $this->expectException(IncorrectDataException::class);
        $clientRequest = new ClientRequest;
        $clientRequest->fromArray(['email' => '', 'data' => 'dataString']);
    }

    public function testWrongEmail()
    {
        $this->expectException(IncorrectDataException::class);
        $clientRequest = new ClientRequest;
        $clientRequest->fromArray(['email' => 123, 'data' => 'dataString']);
    }

    public function testSettersGetters()
    {
        $clientRequest = new ClientRequest('email@mail.ru', 'dataString');

        $clientRequest->setEmail('email');
        $this->assertEquals('email', $clientRequest->getEmail());

        $clientRequest->setData('data');
        $this->assertEquals('data', $clientRequest->getData());
    }

}
