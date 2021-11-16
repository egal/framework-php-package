<?php

declare(strict_types=1);

namespace Egal\Core\Bus;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Exceptions\MessageProcessingException;
use Egal\Core\Exceptions\TargetQueueNotProvidedException;
use Egal\Core\Exceptions\UnableDetermineMessageTypeException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\EventMessage;
use Egal\Core\Messages\HasActionMessageInterface;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Egal\Exception\HasInternalCode;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class RabbitMQBus extends Bus
{

    protected const REPLY_TO_PROPERTY_NAME = 'reply_to';

    protected RabbitMQQueue $connection;

    protected string $queueName;

    protected string $replyQueueName;

    protected bool $replyQueueExists = false;

    public function __construct()
    {
        $connector = new RabbitMQConnector(app('events'));
        $this->connection = $connector->connect(config('queue.connections.rabbitmq'));
        $this->replyQueueName = config('app.service_name') . '.' . Str::uuid() . '.service.request_reply';
    }

    public function publishMessage(Message $message): void
    {
        $this->basicPublish($message);
    }

    public function startProcessingMessages(): void
    {
        $serviceName = config('app.service_name');
        $this->queueName = $serviceName . '.service';

        $this->connection->declareQueue(
            $this->queueName,
            false,
            false,
            ['x-queue-mode' => 'default']
        );
        $this->connection->getChannel()->queue_bind(
            $this->queueName,
            'amq.direct',
            $serviceName . '.action'
        );
    }

    public function stopProcessingMessages(): void
    {
    }

    public function processMessages(): void
    {
        $this->connection->getChannel()->basic_qos(null, 1, null);
        $this->connection->getChannel()->basic_consume(
            $this->queueName,
            '',
            true,
            true,
            false,
            false,
            fn(AMQPMessage $message) => $this->processMessage($message)
        );

        while (true) {
            $this->connection->getChannel()->wait();
        }
    }

    public function startConsumeReplyMessages(ActionMessage $actionMessage, callable $callback): void
    {
        if (!$this->replyQueueExists) {
            $this->connection->getChannel()->queue_declare(
                $this->replyQueueName,
                false,
                false,
                true,
                true,
                true,
                new AMQPTable([
                    'x-queue-mode' => 'default',
                    'x-expires' => config('app.request.wait_reply_message_ttl') * 1000,
                ]),
                null
            );

            $this->connection->getChannel()->basic_qos(null, 1, null);
            $this->connection->getChannel()->basic_consume(
                $this->replyQueueName,
                $this->replyQueueName,
                true,
                true,
                true,
                true,
                null
            );

            $this->replyQueueExists = true;
        }

        $convertJsonToMessage = static function (string $body): Message {
            $body = json_decode($body, true);

            if (!array_key_exists('type', $body)) {
                throw new UnableDetermineMessageTypeException();
            }

            switch ($body['type']) {
                case MessageType::START_PROCESSING:
                    return StartProcessingMessage::fromArray($body);
                case MessageType::ACTION_RESULT:
                    return ActionResultMessage::fromArray($body);
                case MessageType::ACTION_ERROR:
                    return ActionErrorMessage::fromArray($body);
                default:
                    throw new UnsupportedMessageTypeException();
            }
        };

        $this->connection->getChannel()->callbacks[$this->replyQueueName] =
            static fn(AMQPMessage $message) => $callback($convertJsonToMessage($message->body));
    }

    public function stopConsumeReplyMessages(ActionMessage $actionMessage): void
    {
        $this->connection->getChannel()->callbacks[$this->replyQueueName] = static function (AMQPMessage $message): void {
            // Since the consumer remains, but the handler needs to be turned off, we'll just make the callback empty.
        };
    }

    public function consumeReplyMessages(float $timeout = 0): void
    {
        try {
            $this->connection->getChannel()->wait(null, false, $timeout);
        } catch (AMQPTimeoutException $exception) {
            // This error is not critical, since it is a stopping point for processing the queue.
        }
    }

    protected function basicPublish(Message $message, ?string $targetQueue = null): void
    {
        $exchange = 'amq.direct';

        if ($message instanceof ActionMessage) {
            $routingKey = $message->getServiceName() . '.' . $message->getType();
        } elseif ($message instanceof HasActionMessageInterface) {
            if (!$targetQueue) {
                throw new TargetQueueNotProvidedException();
            }

            $routingKey = $targetQueue;
            $exchange = '';
        } elseif ($message instanceof EventMessage) {
            $routingKey = $message->getType();
        } else {
            throw new UnsupportedMessageTypeException();
        }

        $properties = ['delivery_mode' => 1];

        if ($message instanceof ActionMessage) {
            $properties[self::REPLY_TO_PROPERTY_NAME] = $this->replyQueueName;
        }

        $AMQPMessage = new AMQPMessage($message->toJson(), $properties);
        $this->connection->getChannel()->basic_publish($AMQPMessage, $exchange, $routingKey);
    }

    private function processMessage(AMQPMessage $message): void
    {
        $body = json_decode($message->body, true);

        if (!isset($body['type'])) {
            throw new MessageProcessingException();
        }

        switch ($body['type']) {
            case MessageType::ACTION:
                $this->processActionMessage($body, $message);
                break;
            case MessageType::EVENT:
                throw new MessageProcessingException('НЕ РЕАЛИЗОВАНО!');
            // TODO: Реализовать.
            default:
                throw new MessageProcessingException('Error processing queue message! ' . json_encode($body));
        }
    }

    /**
     * @param mixed $body
     * @throws \Egal\Core\Exceptions\TargetQueueNotProvidedException
     * @throws \Egal\Core\Exceptions\UnsupportedMessageTypeException
     * @throws \Egal\Core\Exceptions\InitializeMessageFromArrayException
     * @throws \Egal\Core\Exceptions\TokenSignatureInvalidException
     * @throws \Egal\Core\Exceptions\UndefinedTypeOfMessageException
     */
    private function processActionMessage($body, AMQPMessage $message): void
    {
        $actionMessage = ActionMessage::fromArray($body);
        $replyTo = $message->get(self::REPLY_TO_PROPERTY_NAME);
        $startProcessingMessage = new StartProcessingMessage();
        $startProcessingMessage->setActionMessage($actionMessage);
        $this->basicPublish($startProcessingMessage, $replyTo);
        Session::setActionMessage($actionMessage);

        try {
            $actionResultMessage = new ActionResultMessage();
            $actionResultMessage->setActionMessage($actionMessage);
            $actionCaller = new ActionCaller(
                $actionMessage->getModelName(),
                $actionMessage->getActionName(),
                $actionMessage->getParameters()
            );
            $actionResultMessage->setData($actionCaller->call());
            $this->basicPublish($actionResultMessage, $replyTo);
        } catch (Throwable $exception) {
            $actionErrorMessage = new ActionErrorMessage();
            $actionErrorMessage->setMessage($exception->getMessage());

            switch (get_class($exception)) {
                case QueryException::class:
                    $actionErrorMessage->setCode(500);
                    break;
                default:
                    $actionErrorMessage->setCode($exception->getCode());
                    break;
            }

            $actionErrorMessage->setActionMessage(Session::getActionMessage());

            if ($exception instanceof HasInternalCode) {
                $actionErrorMessage->setInternalCode($exception->getInternalCode());
            }

            $this->basicPublish($actionErrorMessage, $replyTo);
        }

        Session::unsetActionMessage();
    }

}
