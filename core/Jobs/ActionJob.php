<?php

declare(strict_types=1);

namespace Egal\Core\Jobs;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Bus\Bus;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ActionJob
 */
class ActionJob extends Job
{

    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @deprecated since v2.0.0
     */
    public const MODEL_NAMESPACE = '\App\Models\\';

    /**
     * @deprecated since v2.0.0
     */
    public const MODEL_ACTION_PREFIX = 'action';

    /**
     * Processed message
     */
    private ActionMessage $actionMessage;

    /**
     * Processing result
     */
    private ActionResultMessage $actionResultMessage;

    /**
     * @param mixed[] $data
     * @throws \Egal\Core\Exceptions\ActionCallException|\Illuminate\Contracts\Container\BindingResolutionException|\Egal\Core\Exceptions\InitializeMessageFromArrayException|\ReflectionException|\Egal\Core\Exceptions\UndefinedTypeOfMessageException|\Egal\Core\Exceptions\TokenSignatureInvalidException|\Egal\Auth\Exceptions\UndefinedTokenTypeException|\Egal\Auth\Exceptions\InitializeServiceServiceTokenException|\Egal\Auth\Exceptions\InitializeUserServiceTokenException|\Egal\Auth\Exceptions\TokenExpiredException|\Egal\Core\Exceptions\NoAccessActionCallException|\Exception
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $data): void
    {
        $rabbitMQJob->delete();
        $this->setActionMessage(ActionMessage::fromArray($data));
        $this->publishMessageStartProcessing();
        Session::setActionMessage($this->getActionMessage());
        $this->configureActionMessageResult();

        $actionCaller = new ActionCaller(
            $this->actionMessage->getModelName(),
            $this->actionMessage->getActionName(),
            $this->actionMessage->getParameters()
        );

        $result = $actionCaller->call();

        $this->actionResultMessage->setData($result);
        Bus::getInstance()->publishMessage($this->actionResultMessage);
        Session::unsetActionMessage();
    }

    /**
     * Configure {@see \Egal\Core\Jobs\ActionJob::$actionResultMessage}.
     *
     * Configure {@see \Egal\Core\Messages\ActionResultMessage} class
     * and write to {@see \Egal\Core\Jobs\ActionJob::$actionResultMessage}.
     */
    public function configureActionMessageResult(): void
    {
        $this->actionResultMessage = new ActionResultMessage();
        $this->actionResultMessage->setActionMessage($this->actionMessage);
    }

    /**
     * Configure {@see \Egal\Core\Messages\StartProcessingMessage::$actionResultMessage}
     */
    public function publishMessageStartProcessing(): void
    {
        $messageStartProcessing = new StartProcessingMessage();
        $messageStartProcessing->setActionMessage($this->actionMessage);
        Bus::getInstance()->publishMessage($messageStartProcessing);
    }

    /**
     * Getter for {@see ActionJob::$actionMessage}.
     */
    public function getActionMessage(): ActionMessage
    {
        return $this->actionMessage;
    }

    /**
     * Setter for {@see ActionJob::$actionMessage}.
     */
    public function setActionMessage(ActionMessage $actionMessage): void
    {
        $this->actionMessage = $actionMessage;
    }

    /**
     * @deprecated since v2.0.0, because unused.
     */
    private function getModelClassName(): string
    {
        return $this->actionMessage->getModelName();
    }

    /**
     * @deprecated since v2.0.0, {@see ActionCaller} class will be responsible for this.
     */
    private function getActionFullName(): string
    {
        return self::MODEL_ACTION_PREFIX . ucwords($this->actionMessage->getActionName());
    }

}
