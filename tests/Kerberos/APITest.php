<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Common\Interfaces\Kerberos\KDCResponseInterface;
use EgalFramework\Kerberos\API;
use EgalFramework\Kerberos\Crypt;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use EgalFramework\Kerberos\Exceptions\MandateInvalidException;
use EgalFramework\Kerberos\Exceptions\TimeShiftException;
use EgalFramework\Kerberos\KDCResponse;
use EgalFramework\Kerberos\SessionKey;
use EgalFramework\Kerberos\Tests\Samples\User;
use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{

    private API $api;
    private Crypt $encrypt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->api = new API;
        $this->encrypt = new Crypt;
    }

    /**
     * @throws IncorrectDataException
     */
    public function testClientRequestFromArray()
    {
        $clientRequest = $this->api->getClientRequestFromArray(['email' => 'email', 'data' => 'data']);
        $this->assertEquals(['email' => 'email', 'data' => 'data'], $clientRequest->toArray());
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function testCreateMandate()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(time(), $user->password)]
        );
        $mandate = $this->api->createKDCResponse($clientRequest, $user, 'pass', 100);
        $this->assertNotEmpty($mandate->getSessionKey());
        $this->assertNotEmpty($mandate->getMandate());
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function testNoTimeSpecified()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(0, $user->password)]
        );
        $this->expectException(IncorrectDataException::class);
        $this->api->createKDCResponse($clientRequest, $user, 'pass', 100);
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function testTimeShiftLess()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(time() - 301, $user->password)]
        );
        $this->expectException(TimeShiftException::class);
        $this->api->createKDCResponse($clientRequest, $user, 'pass', 100);
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function testTimeShiftMore()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(time() + 320, $user->password)]
        );
        $this->expectException(TimeShiftException::class);
        $this->api->createKDCResponse($clientRequest, $user, 'pass', 10);
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function testTimeShift()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(time(), $user->password)]
        );
        $this->api->createKDCResponse($clientRequest, $user, 'pass', 100);
        $this->assertTrue(true);
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     */
    public function testCheckKDCResponse()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(time(), $user->password)]
        );
        $mandate = $this->api->createKDCResponse($clientRequest, $user, 'pass', 100);
        $response = new KDCResponse(
            new SessionKey('email', $this->encrypt->encrypt(time(), $user->password)),
            json_encode($mandate->toArray())
        );
        $this->api->checkKDCResponse($response, $user);
        $this->assertTrue(true);
    }

    /**
     * @throws IncorrectDataException
     * @throws TimeShiftException
     * @throws MandateInvalidException
     */
    public function testGetMandate()
    {
        $user = new User('pass');
        $clientRequest = $this->api->getClientRequestFromArray(
            ['email' => 'email', 'data' => $this->encrypt->encrypt(time(), $user->password)]
        );
        $kdcResponse = $this->api->createKDCResponse($clientRequest, $user, 'pass', 100);
        $newMandate = $this->api->getMandate($kdcResponse->getMandate(), $user->password);
        $this->assertNotEmpty($newMandate->getSessionKey());
    }

    /**
     * @throws IncorrectDataException
     * @throws MandateInvalidException
     */
    public function testEmptyMandateError()
    {
        $this->expectException(MandateInvalidException::class);
        $this->api->getMandate('{"initVector":"12312312312312312312312312312312","salt":"123123","data":"data"}', 'password');
    }

    /**
     * @throws IncorrectDataException
     * @throws MandateInvalidException
     */
    public function testWrongJSONMandateError()
    {
        $this->expectException(MandateInvalidException::class);
        $this->api->getMandate($this->encrypt->encrypt('data', 'password'), 'password');
    }

    public function testNewKDCResponse()
    {
        $this->assertInstanceOf(KDCResponseInterface::class, $this->api->getNewKDCResponse());
    }

}
