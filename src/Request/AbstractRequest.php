<?php

namespace EgalFramework\Request;

use EgalFramework\Common\HTTP;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use EgalFramework\Request\Exceptions\RequestFailedException;
use Exception;
use Ramsey\Uuid\Uuid;

abstract class AbstractRequest
{

    /**
     * @var string Current app name
     */
    protected string $thisAppName;

    /**
     * @var MessageInterface Temporary variable for returned response via callback
     */
    protected MessageInterface $response;

    public function __construct(string $appName)
    {
        $this->thisAppName = $appName;
    }

    public function send(string $serviceName, MessageInterface $message)
    {
        $queue = Session::getQueue();
        $queue->setPath($serviceName);
        $poolName = '_model_' . $message->getModel();
        if (!in_array($poolName, $queue->getPools())) {
            $queue->createPool($poolName);
        }
        $queue->send($serviceName, $poolName, $message);
    }

    /**
     * @param string $model
     * @param string $action
     * @param int $method
     * @return MessageInterface
     * @throws Exception
     */
    public function createMessage(string $model, string $action, int $method = HTTP::METHOD_POST): MessageInterface
    {
        $message = Session::getQueue()->getNewMessageInstance();
        $message->setMethod($method);
        $message->setAction($action);
        $message->setModel($model);
        $message->setUid(Uuid::uuid4()->toString());
        $message->setSender(Settings::getAppName());
        return $message;
    }

    /**
     * @param string $serviceName
     * @param string $uid
     * @return MessageInterface
     * @throws RequestFailedException
     */
    public function read(string $serviceName, string $uid): MessageInterface
    {
        $callback = function (string $data) {
            $this->response = Session::getQueue()->getNewMessageInstance();
            $this->response->fromJSON($data);
        };
        $callback->bindTo($this);
        if (!Session::getQueue()->read($serviceName, $uid, $callback)) {
            throw new RequestFailedException(sprintf('Failed to get data from %s request', $serviceName), 401);
        }
        return $this->response;
    }

}
