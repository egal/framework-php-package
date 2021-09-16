<?php

namespace Egal\Core\Handlers;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Bus\Bus;
use Egal\Core\Exceptions\ActionCallException;
use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use ReflectionException;

/**
 * Class ActionHandler
 * @package Egal\Core\Handlers
 */
class ActionHandler implements HandlerInterface
{
    private ActionMessage $actionMessage;
    private ActionResultMessage $actionResultMessage;

    /**
     * @param array $data
     * @throws ActionCallException
     * @throws InitializeMessageFromArrayException
     * @throws ReflectionException
     * @throws UndefinedTypeOfMessageException
     * @throws \Egal\Auth\Exceptions\InitializeServiceServiceTokenException
     * @throws \Egal\Auth\Exceptions\InitializeUserServiceTokenException
     * @throws \Egal\Auth\Exceptions\TokenExpiredException
     * @throws \Egal\Auth\Exceptions\UndefinedTokenTypeException
     * @throws \Egal\Core\Exceptions\TokenSignatureInvalidException
     * @throws \Exception
     */
    public function handle(array $data): void
    {
        $this->setActionMessage(ActionMessage::fromArray($data));
        $this->publishMessageStartProcessing();

        try {
            Session::setActionMessage($this->getActionMessage());
            $this->configureActionMessageResult();

            $result = (new ActionCaller(
                $this->actionMessage->getModelName(),
                $this->actionMessage->getActionName(),
                $this->actionMessage->getParameters()
            ))->call();

            $this->actionResultMessage->setData($result);
            Bus::getInstance()->publishMessage($this->actionResultMessage);
        } catch (\Throwable $exception) {
            report($exception);
        }

        Session::unsetActionMessage();
    }

    public function configureActionMessageResult()
    {
        $this->actionResultMessage = new ActionResultMessage();
        $this->actionResultMessage->setActionMessage($this->actionMessage);
    }

    public function publishMessageStartProcessing()
    {
        $messageStartProcessing = new StartProcessingMessage();
        $messageStartProcessing->setActionMessage($this->actionMessage);
        Bus::getInstance()->publishMessage($messageStartProcessing);
    }

    public function getActionMessage(): ActionMessage
    {
        return $this->actionMessage;
    }

    public function setActionMessage(ActionMessage $actionMessage): void
    {
        $this->actionMessage = $actionMessage;
    }
}
