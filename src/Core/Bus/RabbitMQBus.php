<?php

namespace Egal\Core\Bus;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Exceptions\MessageProcessingException;
use Egal\Core\Exceptions\UnableDetermineMessageTypeException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\EventMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Session\Session;
use Exception;
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

    private RabbitMQQueue $connection;
    public string $queueName;
    private string $replyQueueName;
    private bool $replyQueueExists = false;

    public function __construct()
    {
        $connector = new RabbitMQConnector(app('events'));
        $this->connection = $connector->connect(config('queue.connections.rabbitmq'));
        $this->replyQueueName = config('app.service_name') . '.' . Str::uuid() . '.reply_queue';
    }

    public function publishMessage(Message $message): void
    {
        if (
            !($routingKey = $this->getRoutingKey($message))
            || !($exchange = $this->getExchange($message))
        ) {
            throw new Exception('Невозможно опубликовать ' . get_class($message));
        }

        $properties = ['delivery_mode' => 1];

        if ($message instanceof ActionMessage) {
            $properties['delivery_mode'] = 2;
            $properties['reply_to'] = $this->replyQueueName;
            $properties['application_headers'] = new AMQPTable(['hash-on' => $message->getUuid()]);
        }

        $AMQPMessage = new AMQPMessage($message->toJson(), $properties);
        $this->connection->getChannel()->basic_publish($AMQPMessage, $exchange, $routingKey);
    }

    protected function getExchange(Message $message): string
    {
        switch ($message->getType()) {
            case MessageType::ACTION:
            case MessageType::EVENT:
                return 'amq.topic';
            case MessageType::ACTION_RESULT:
            case MessageType::ACTION_ERROR:
            case MessageType::START_PROCESSING:
                return 'amq.direct';
            default:
                throw new UnsupportedMessageTypeException();
        }
    }

    protected function getRoutingKey(Message $message): string
    {
        switch ($message->getType()) {
            case MessageType::ACTION:
                /** @var ActionMessage $message */
                return words_to_dot_case(
                    $message->getServiceName(),
                    $message->getModelName(),
                    $message->getActionName(),
                    $message->getType()
                );
            case MessageType::EVENT:
                /** @var EventMessage $message */
                return words_to_dot_case(
                    $message->getServiceName(),
                    $message->getModelName(),
                    $message->getId(),
                    $message->getName(),
                    $message->getType()
                );
            case MessageType::ACTION_RESULT:
            case MessageType::ACTION_ERROR:
            case MessageType::START_PROCESSING:
                /** @var ActionResultMessage|ActionErrorMessage|StartProcessingMessage $message */
                return $message->getActionMessage()->getUuid();
            default:
                throw new UnsupportedMessageTypeException();
        }
    }

    public function startProcessingMessages(): void
    {
        $processUuid = Str::uuid()->toString();
        $serviceName = config('app.service_name');
        $this->queueName = "$serviceName.$processUuid.queue";
        $exchangeBalancerName = "$serviceName.balancer.exchange";

        $this->connection->declareQueue(
            $this->queueName,
            false,
            true,
            ['x-queue-mode' => 'default']
        );
        $this->connection->declareExchange(
            $exchangeBalancerName,
            'x-consistent-hash',
            false,
            false,
            ['hash-header' => 'hash-on']
        );
        $this->connection->bindQueue(
            $this->queueName,
            $exchangeBalancerName,
            '100'
        );
        $this->connection->getChannel()->exchange_bind(
            $exchangeBalancerName,
            'amq.topic',
            "$serviceName.*.*.action"
        );
        $this->connection->getChannel()->exchange_bind(
            $exchangeBalancerName,
            'amq.topic',
            '*.*.*.*.event'
        );
    }

    public function stopProcessingMessages(): void
    {
    }

    public function processMessages(): void
    {
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

    private function processMessage(AMQPMessage $message)
    {
        $body = json_decode($message->body, true);
        if (!isset($body['type'])) {
            throw new MessageProcessingException();
        }

        switch ($body['type']) {
            case MessageType::ACTION:
                $actionMessage = ActionMessage::fromArray($body);

                $startProcessingMessage = new StartProcessingMessage();
                $startProcessingMessage->setActionMessage($actionMessage);
                $this->connection->getChannel()->basic_publish(
                    new AMQPMessage($startProcessingMessage->toJson(), ['delivery_mode' => 1]),
                    '',
                    $message->get('reply_to')
                );

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
                    $this->connection->getChannel()->basic_publish(
                        new AMQPMessage($actionResultMessage->toJson(), ['delivery_mode' => 1]),
                        '',
                        $message->get('reply_to')
                    );
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
                    $this->connection->getChannel()->basic_publish(
                        new AMQPMessage($actionErrorMessage->toJson(), ['delivery_mode' => 1]),
                        '',
                        $message->get('reply_to')
                    );
                }

                Session::unsetActionMessage();
                break;
            case MessageType::EVENT:
                throw new MessageProcessingException('НЕ РЕАЛИЗОВАНО!'); # TODO: Реализовать.
            default:
                throw new MessageProcessingException('Error processing queue message! ' . json_encode($body));
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

            $this->connection->getChannel()->basic_consume(
                $this->replyQueueName,
                $this->replyQueueName,
                true,
                true,
                true,
                false,
                function (AMQPMessage $message) {
                }
            );

            $this->replyQueueExists = true;
        }

        $convertJsonToMessage = function (string $body) {
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
            fn(AMQPMessage $message) => $callback($convertJsonToMessage($message->body));
    }

    public function stopConsumeReplyMessages(ActionMessage $actionMessage): void
    {
    }

    public function consumeReplyMessages($timeout = 0): void
    {
        try {
            $this->connection->getChannel()->wait(null, false, $timeout);
        } catch (AMQPTimeoutException $exception) {

        }
    }

}
