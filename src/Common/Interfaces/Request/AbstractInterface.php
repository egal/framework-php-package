<?php

namespace EgalFramework\Common\Interfaces\Request;

use EgalFramework\Common\HTTP;
use EgalFramework\Common\Interfaces\MessageInterface;

interface AbstractInterface
{

    public function createMessage(string $model, string $action, int $method = HTTP::METHOD_POST): MessageInterface;

    public function read(string $serviceName, string $uid): MessageInterface;

}
