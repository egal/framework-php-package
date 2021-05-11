<?php

namespace EgalFramework\Request;

use EgalFramework\Kerberos\ClientRequest;
use EgalFramework\Kerberos\Common;
use EgalFramework\Kerberos\Crypt;
use EgalFramework\Request\Exceptions\RequestFailedException;
use Exception;

class Auth extends AbstractRequest
{

    private string $authServiceName;

    private string $password;

    private array $token;

    public function __construct(string $appName, string $password, string $authServiceName = null)
    {
        if (empty($authServiceName)) {
            $authServiceName = env('AUTH_SERVICE_NAME');
        }
        $this->authServiceName = $authServiceName;
        $this->password = $password;
        parent::__construct($appName);
    }

    /**
     * @throws RequestFailedException
     * @throws Exception
     */
    public function run(): void
    {
        $crypt = new Crypt;
        if ($this->checkLogged()) {
            return;
        }
        $this->token = (new ClientRequest($this->thisAppName, $crypt->encrypt(time(), $this->password)))->toArray();
        $message = $this->createMessage('Service', 'login');
        $message->setData([$this->token]);
        $this->send($this->authServiceName, $message);
        $this->read($this->authServiceName, $message->getUid());
        $this->processError();
        $time = $crypt->decrypt($this->response->getData()[Common::FIELD_DATA], $this->password);
        if (($time > time() + 5 * 60) || ($time < time() - 5 * 60)) {
            throw new Exception('Time shift, auth server is invalid', 401);
        }
    }

    /**
     * @return bool
     * @throws RequestFailedException
     * @throws Exception
     */
    private function checkLogged()
    {
        if (!isset($this->token)) {
            return false;
        }
        $message = $this->createMessage('Service', 'checkLogged');
        $message->setData([$this->token]);
        $this->send($this->authServiceName, $message);
        $this->read($this->authServiceName, $message->getUid());
        $data = $this->response->getData();
        if (is_array($data) && isset($data['error'])) {
            throw new RequestFailedException($data['error']['message'], $data['error']['code']);
        }
        return (bool)$data;
    }

    /**
     * @throws RequestFailedException
     */
    private function processError(): void
    {
        $data = $this->response->getData();
        if (!is_array($data)) {
            throw new RequestFailedException(
                sprintf('Incorrect data received from auth service: %s', var_export($data, true)), 500
            );
        }
        if (!isset($data['error'])) {
            return;
        }
        throw new RequestFailedException($data['error']['message'], $data['error']['code']);
    }

    public function getSessionKey(): ?array
    {
        return isset($this->token)
            ? $this->token
            : null;
    }

}
