<?php

namespace EgalFramework\Common\Interfaces\Request;

use EgalFramework\Common\Interfaces\MessageInterface;

interface SendInterface extends AbstractInterface
{

    public function __construct(string $appName, string $password = '', string $authServiceName = 'auth');

    public function send(string $serviceName, MessageInterface $message): void;

}
