<?php

namespace EgalFramework\Request;

use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\Request\SendInterface;

class Send extends AbstractRequest implements SendInterface
{

    private Auth $auth;

    public function __construct(string $appName, string $password = '', string $authServiceName = null)
    {
        if (empty($authServiceName)) {
            $authServiceName = env('AUTH_SERVICE_NAME');
        }
        if (!empty($password)) {
            $this->auth = new Auth($appName, $password, $authServiceName);
        }
        parent::__construct($appName);
    }

    /**
     * @param string $serviceName
     * @param MessageInterface $message
     * @throws Exceptions\RequestFailedException
     */
    public function send(string $serviceName, MessageInterface $message): void
    {
        if ($this->auth) {
            $this->auth->run();
        }
        $token = $this->auth->getSessionKey();
        if (!is_null($token)) {
            $message->setMandate(json_encode($token, JSON_UNESCAPED_SLASHES));
        }
        parent::send($serviceName, $message);
    }

}
