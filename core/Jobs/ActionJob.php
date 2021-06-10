<?php

namespace Egal\Core\Jobs;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Bus\Bus;
use Egal\Core\Exceptions\ActionCallException;
use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionException;

/**
 * Class ActionJob
 * @package Egal\Core\Jobs
 */
class ActionJob extends Job
{

    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public const MODEL_NAMESPACE = '\App\Models\\';

    /**
     * @deprecated
     */
    public const MODEL_ACTION_PREFIX = 'action';

    private ActionMessage $actionMessage;
    private ActionResultMessage $actionResultMessage;

    /**
     * @param RabbitMQJob $rabbitMQJob
     * @param array $data
     * @throws ActionCallException
     * @throws BindingResolutionException
     * @throws InitializeMessageFromArrayException
     * @throws ReflectionException
     * @throws UndefinedTypeOfMessageException
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $data): void
    {
        $rabbitMQJob->delete();
        $this->setActionMessage(ActionMessage::fromArray($data));
        Session::setActionMessage($this->getActionMessage());
        $this->publishMessageStartProcessing();
        $this->configureActionMessageResult();

        $result = (new ActionCaller(
            $this->actionMessage->getModelName(),
            $this->actionMessage->getActionName(),
            $this->actionMessage->getParameters()
        ))->call();

        $this->actionResultMessage->setData($result);
        Bus::getInstance()->publishMessage($this->actionResultMessage);
        Session::unsetActionMessage();
    }

    /**
     * @return string
     * @deprecated
     */
    private function getModelClassName(): string
    {
        return $this->actionMessage->getModelName();
    }

    /**
     * @return string
     * @deprecated
     */
    private function getActionFullName(): string
    {
        return self::MODEL_ACTION_PREFIX . ucwords($this->actionMessage->getActionName());
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
