<?php

namespace EgalFramework\Kerberos;

use EgalFramework\Kerberos\Exceptions\IncorrectDataException;

/**
 * Class Encrypted
 * @package EgalFramework\Kerberos
 */
class Crypt
{

    public function encrypt(string $data, string $password): string
    {
        $salt = openssl_random_pseudo_bytes(8);
        $salted = '';
        $dx = '';
        while (strlen($salted) < 48) {
            $dx = md5($dx . $password . $salt, true);
            $salted .= $dx;
        }
        $iv = substr($salted, 32, 16);
        $encryptedData = openssl_encrypt(
            json_encode($data), Common::ENCRYPT_ALGORITHM, substr($salted, 0, 32), true, $iv
        );
        return json_encode([
            'data' => base64_encode($encryptedData),
            'initVector' => bin2hex($iv),
            'salt' => bin2hex($salt)
        ]);
    }

    /**
     * @param string $data
     * @param string $password
     * @return mixed
     * @throws IncorrectDataException
     */
    public function decrypt(string $data, string $password): string
    {
        $arr = json_decode($data, true);
        if (json_last_error()) {
            throw new IncorrectDataException('JSON data is incorrect: ' . json_last_error_msg());
        }
        $this->checkData($arr);
        $passphrase = $password . hex2bin($arr['salt']);
        $md5 = [];
        $md5[0] = md5($passphrase, true);
        $result = $md5[0];
        for ($i = 1; $i < 3; $i++) {
            $md5[$i] = md5($md5[$i - 1] . $passphrase, true);
            $result .= $md5[$i];
        }
        $json = openssl_decrypt(
            base64_decode($arr['data']), Common::ENCRYPT_ALGORITHM, substr($result, 0, 32), true,
            hex2bin($arr['initVector'])
        );
        return (string)json_decode($json, true);
    }

    /**
     * @param array $arr
     * @throws IncorrectDataException
     */
    private function checkData(array $arr): void
    {
        if (empty($arr[Common::FIELD_INIT_VECTOR])) {
            throw new IncorrectDataException('No init vector specified');
        }
        if (empty($arr[Common::FIELD_SALT])) {
            throw new IncorrectDataException('No salt specified');
        }
        if (empty($arr[Common::FIELD_DATA])) {
            throw new IncorrectDataException('No encrypted data specified');
        }
    }

}
