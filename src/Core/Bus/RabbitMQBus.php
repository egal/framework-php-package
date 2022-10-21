<?php

declare(strict_types=1);

namespace Egal\Core\Bus;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EventHandlingException;
use Egal\Core\Exceptions\MessageProcessingException;
use Egal\Core\Exceptions\TargetQueueNotProvidedException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\EventMessage;
use Egal\Core\Messages\HasActionMessageInterface;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageCreator;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Egal\Exception\HasData;
use Egal\Exception\HasInternalCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Exception\AMQPTimeoutException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Throwable;

/**
 * @mixin \PhpAmqpLib\Channel\AMQPChannel
 */
class RabbitMQBus extends Bus
{

    private const REPLY_TO_PROPERTY_NAME = 'reply_to';

    private AMQPChannel $channel;

    private string $queueName;

    private string $replyQueueName;

    private bool $replyQueueExists = false;

    public function __construct(array $config)
    {
        $config = Arr::add($config, 'options.heartbeat', 0);

        /** @var \PhpAmqpLib\Connection\AbstractConnection $connector */
        $connector = Arr::get($config, 'connection', AMQPLazyConnection::class);
        $connection = $connector::create_connection(
            Arr::shuffle(Arr::get($config, 'hosts', [])),
            $this->filterOptions(Arr::get($config, 'options', []))
        );

        $this->channel = $connection->channel();
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

        $this->queue_declare(
            $this->queueName,
            false,
            false,
            false,
            false,
            false,
            new AMQPTable(['x-queue-mode' => 'default'])
        );
        $this->queue_bind($this->queueName, 'amq.direct', $serviceName . '.action');
    }

    public function processMessages(): void
    {
        $this->basic_qos(null, 1, null);
        $this->basic_consume(
            $this->queueName,
            '',
            true,
            true,
            false,
            false,
            fn (AMQPMessage $message) => $this->processMessage($message)
        );

        while (true) {
            $this->wait();
        }
    }

    public function stopProcessingMessages(): void
    {
        $this->close();
    }

    public function startConsumeReplyMessages(callable $callback): void
    {
        if (!$this->replyQueueExists) {
            $this->queue_declare(
                $this->replyQueueName,
                false,
                false,
                true,
                true,
                false,
                new AMQPTable([
                    'x-queue-mode' => 'default',
                    'x-expires' => config('app.request.wait_reply_message_ttl') * 1000,
                ]),
                null
            );

            $this->basic_qos(null, 1, null);
            $this->basic_consume($this->replyQueueName, $this->replyQueueName, true, true, true, true);

            $this->replyQueueExists = true;
        }

        $callback = static fn (AMQPMessage $message) => $callback(MessageCreator::fromJson($message->body));
        $this->channel->callbacks[$this->replyQueueName] = $callback;
    }

    public function consumeReplyMessages(float $timeout = 0): void
    {
        try {
            $this->wait(null, false, $timeout);
        } catch (AMQPTimeoutException $exception) {
            // This error is not critical, since it is a stopping point for processing the queue.
            return;
        }
    }

    public function stopConsumeReplyMessages(): void
    {
        // Since the consumer remains, but the handler needs to be turned off, we'll just make the callback empty.
        $this->channel->callbacks[$this->replyQueueName] = null;
    }

    private function basicPublish(Message $message, ?string $targetQueue = null): void
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

        $properties = ['delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT];

        if ($message instanceof ActionMessage) {
            $properties[self::REPLY_TO_PROPERTY_NAME] = $this->replyQueueName;
        }

        $AMQPMessage = new AMQPMessage($message->toJson(), $properties);
        $this->basic_publish($AMQPMessage, $exchange, $routingKey);
    }

    private function processMessage(AMQPMessage $message): void
    {
        $body = json_decode($message->body, true);
        $type = Arr::get($body, 'type');

        if (!$type) {
            throw new MessageProcessingException();
        }

        switch ($type) {
            case MessageType::ACTION:
                $this->processActionMessage($body, $message);
                break;
            case MessageType::EVENT:
                $this->processEventMessage($body);
                break;
            default:
                throw new MessageProcessingException('Error processing queue message! ' . json_encode($body));
        }
    }

    /**
     * @throws \Egal\Core\Exceptions\TargetQueueNotProvidedException
     * @throws \Egal\Core\Exceptions\UnsupportedMessageTypeException
     * @throws \Egal\Core\Exceptions\InitializeMessageFromArrayException
     * @throws \Egal\Core\Exceptions\AuthenticationFailedException
     * @throws \Egal\Core\Exceptions\UndefinedTypeOfMessageException
     */
    private function processActionMessage(array $body, AMQPMessage $message): void
    {
        Log::info('Action processing started', ['body' => $body]);

        $actionMessage = ActionMessage::fromArray($body);
        $replyTo = $message->get(self::REPLY_TO_PROPERTY_NAME);
        $startProcessingMessage = new StartProcessingMessage();
        $startProcessingMessage->setActionMessage($actionMessage);
        $this->basicPublish($startProcessingMessage, $replyTo);

        try {
            Session::setActionMessage($actionMessage);
            $actionResultMessage = new ActionResultMessage();
            $actionResultMessage->setActionMessage($actionMessage);
            $actionCaller = new ActionCaller(
                $actionMessage->getModelName(),
                $actionMessage->getActionName(),
                $actionMessage->getParameters()
            );
            $actionResultMessage->setData($actionCaller->call());

            Log::info('Action result', ['body' => $actionResultMessage->toArray()]);

            $this->basicPublish($actionResultMessage, $replyTo);
        } catch (Throwable $exception) {
            report($exception);

            $actionErrorMessage = new ActionErrorMessage();
            $actionErrorMessage->setMessage($exception->getMessage());

            $actionErrorMessage->setCode(is_string($exception->getCode()) ? 500 : $exception->getCode());

            if ($exception instanceof HasData) {
                $actionErrorMessage->setData($exception->getData());
            }

            $actionErrorMessage->setActionMessage(Session::getActionMessage());

            if ($exception instanceof HasInternalCode) {
                $actionErrorMessage->setInternalCode($exception->getInternalCode());
            }

            Log::info('Action error caught', ['body' => $actionErrorMessage->toArray()]);

            $this->basicPublish($actionErrorMessage, $replyTo);
        }

        Session::unsetActionMessage();
    }

    private function filterOptions(array $array): array
    {
        foreach ($array as $index => $value) {
            if (is_array($value)) {
                $array[$index] = $this->filterOptions($value);
            } elseif ($value === null) {
                unset($array[$index]);
            }
        }

        return $array;
    }

    private function processEventMessage(array $body): void
    {
        try {
            $eventMessage = EventMessage::fromArray($body);
            $listenerClasses = EventManager::getListeners(
                $eventMessage->getServiceName(),
                $eventMessage->getModelName(),
                $eventMessage->getName()
            );

            foreach ($listenerClasses as $listenerClass) {
                if (!class_exists($listenerClass)) {
                    throw new EventHandlingException();
                }

                $listener = new $listenerClass();
                $listener->{'handle'}($body['data']);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    public function __call(string $name, array $arguments): void
    {
        $this->channel->{$name}(...$arguments);
    }

}
