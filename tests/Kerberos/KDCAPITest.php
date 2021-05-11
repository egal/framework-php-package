<?php

namespace EgalFramework\Kerberos\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class APITestTest
 */
class KDCAPITest extends TestCase
{

    public function testCreateMandate()
    {
        $user = [
            'email' => 'test@unit.test',
            'password' => '123321'
        ];
        $iv = '676d9cef11d11870';
        $clientResult = [
            'email' => 'test@unit.test',
            'data' => '43add1f34323b6c0140c3cb1d6ca36b9',
            'targetServerKey' => 'auth'
        ];

        $result = $this->createMandate($user, $clientResult['data'], $clientResult['targetServerKey'], $iv);
        $result = json_decode($result, TRUE);

        $this->assertArrayHasKey('mandate', $result);
        $this->assertArrayHasKey('session_key', $result);
        $this->assertArrayHasKey('serverKey', $result);
    }

    /**
     * @param array $user
     * @param string $data
     * @param string $targetServerKey
     * @param string $iv
     * @return false|string
     */
    private function createMandate($user, $data, $targetServerKey, $iv)
    {
        $data = hex2bin($data);
        $cipher = "aes256";

        $time = openssl_decrypt($data, $cipher, $user['password'], 1, $iv);
        if ($time) {
            $sessionKey = [
                'email' => $user['email'],
                'data' => bin2hex($data),
            ];
            unset($user['password']);
            $mandateData = json_encode([
                'sessionKey' => $sessionKey,
                'user' => $user
            ]);

            $mandate = openssl_encrypt($mandateData, $cipher, $targetServerKey, 1, $iv);
            return json_encode([
                'session_key' => $sessionKey,
                'mandate' => bin2hex($mandate),
                'serverKey' => $targetServerKey,
            ]);
        }

        return json_encode([
            'error' => [
                'message' => 'Wrong Requests'
            ]
        ]);
    }

}
