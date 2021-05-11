<?php

namespace EgalFramework\Kerberos\Tests;

use EgalFramework\Kerberos\Crypt;
use EgalFramework\Kerberos\Exceptions\IncorrectDataException;
use PHPUnit\Framework\TestCase;

class EncryptedTest extends TestCase
{

    private Crypt $encrypt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->encrypt = new Crypt;
    }

    /**
     * @throws IncorrectDataException
     */
    public function testEncryption()
    {
        $json = $this->encrypt->encrypt('It\'s alive!', 'superPass1234');
        $data = json_decode($json, true);
        $this->assertNotEmpty($data['data']);
        $this->assertNotEmpty($data['initVector']);
        $this->assertNotEmpty($data['salt']);

        $this->assertEquals('It\'s alive!', $this->encrypt->decrypt($json, 'superPass1234'));
    }

    /**
     * @throws IncorrectDataException
     */
    public function testIncorrectJSON()
    {
        $this->expectException(IncorrectDataException::class);
        $this->encrypt->decrypt('{asdas"', 'qqq');
    }

    /**
     * @throws IncorrectDataException
     */
    public function testNoData()
    {
        $this->expectException(IncorrectDataException::class);
        $this->encrypt->decrypt('{"salt": "qwe", "initVector":"}', 'qwe');
    }

    public function testNoSalt()
    {
        $this->expectException(IncorrectDataException::class);
        $this->encrypt->decrypt('{"data":"data", "initVector": "iv"}', 'pass');
    }

    public function testNoIV()
    {
        $this->expectException(IncorrectDataException::class);
        $this->encrypt->decrypt('{"data":"data", "salt": "salt"}', 'pass');
    }

    public function testWrongData()
    {
        $this->expectException(IncorrectDataException::class);
        $this->encrypt->decrypt('{"salt":"salt", "initVector": "iv"}', 'pass');
    }

    /**
     * @throws IncorrectDataException
     */
    public function testWrongSalt()
    {
        $data = json_decode($this->encrypt->encrypt('It\'s alive!', 'superPass1234'), true);
        $data['salt'] = '1234';
        $this->assertEmpty($this->encrypt->decrypt(json_encode($data), 'superPass1234'));
    }

    /**
     * @throws IncorrectDataException
     */
    public function testWrongIV()
    {
        $data = json_decode($this->encrypt->encrypt('It\'s alive!', 'superPass1234'), true);
        $data['initVector'] = '12341234123412341234123412341234';
        $this->assertEmpty($this->encrypt->decrypt(json_encode($data), 'superPass1234'));
    }

    public function testIncorrectPass()
    {
        $data = json_decode($this->encrypt->encrypt('It\'s alive!', 'superPass1234'), true);
        $this->assertEmpty($this->encrypt->decrypt(json_encode($data), 'superPass1234!'));
    }

}
