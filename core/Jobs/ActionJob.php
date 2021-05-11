<?php

namespace Egal\Core\Jobs;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Bus\Bus;
use Egal\Exception\ActionCallException;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Egal\Model\ModelManager;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionException;

class ActionJob extends Job
{

    use InteractsWithQueue,
        Queueable,
        SerializesModels;

    public const MODEL_NAMESPACE = '\App\Models\\';
    public const MODEL_ACTION_PREFIX = 'action';

    private ActionMessage $actionMessage;
    private ActionResultMessage $actionResultMessage;

    /**
     * @param RabbitMQJob $rabbitMQJob
     * @param array $data
     * @throws ActionCallException
     * @throws ReflectionException
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $data): void
    {
        $rabbitMQJob->delete();
        $this->setActionMessage(ActionMessage::fromArray($data));
        Session::setActionMessage($this->getActionMessage());
        $this->publishMessageStartProcessing();
        $this->configureActionMessageResult();

        $result = ActionCaller::call(
            ModelManager::getModelMetadata($this->getModelClassName())->getModelClass(),
            $this->getActionFullName(),
            $this->actionMessage->getParameters()
        );

        $this->actionResultMessage->setData($result);
        Bus::getInstance()->publishMessage($this->actionResultMessage);
        Session::unsetActionMessage();
    }

    private function getModelClassName(): string
    {
        return $this->actionMessage->getModelName();
    }

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
