<?php

namespace Egal\Core\Bus;

use Egal\Core\ActionCaller\ActionCaller;
use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EventHandlingException;
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
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class RabbitMQBus extends Bus
{

    private RabbitMQConnector $connector;
    private RabbitMQQueue $connection;
    public string $queueName;

    public function __construct()
    {
        $this->connector = new RabbitMQConnector(app('events'));
        $this->connection = $this->connector->connect(config('queue.connections.rabbitmq'));
    }

    private static function getConnection(): RabbitMQQueue
    {
        return (app(Bus::class))->connection;
    }

    public function publishMessage(Message $message): void
    {
        if (
            !($routingKey = $this->getRoutingKey($message))
            || !($exchange = $this->getExchange($message))
        ) {
            throw  new Exception('Невозможно опубликовать ' . get_class($message));
        }

        /* Additional actions before publish */
        switch ($message->getType()) {
            case MessageType::ACTION_ERROR:
            case MessageType::ACTION_RESULT:
            case MessageType::START_PROCESSING:
                /** @var ActionErrorMessage|ActionResultMessage|StartProcessingMessage $message */
                $this->connection->getChannel()->queue_declare(
                    $message->getActionMessage()->getUuid(),
                    true,
                    false,
                    false,
                    true,
                    false,
                    new AMQPTable(["x-queue-mode" => "default"]),
                    null
                );
                $this->connection->getChannel()->queue_bind(
                    $message->getActionMessage()->getUuid(),
                    'amq.direct',
                    $message->getActionMessage()->getUuid()
                );
                break;
            case MessageType::ACTION:
                /** @var ActionMessage $message */
                $this->connection->getChannel()->queue_declare(
                    $message->getUuid(),
                    false,
                    false,
                    false,
                    true,
                    false,
                    new AMQPTable(["x-queue-mode" => "default"]),
                    null
                );
                break;
        }

        $AMQPMessage = new AMQPMessage(
            $message->toJson(),
            [
                'delivery_mode' => 1,
                'application_headers' => new AMQPTable(['hash-on' => $message->getUuid()])
            ]
        );

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

    public function constructEnvironment(): void
    {
        $serviceName = config('app.service_name');
        $this->queueName = "{$serviceName}.queue";

        $this->connection->declareQueue(
            $this->queueName,
            false,
            false,
            ["x-queue-mode" => "default"]
        );

        // Привязываем actions и balancer
        $this->connection->getChannel()->queue_bind(
            $this->queueName,
            'amq.topic',
            "$serviceName.*.*.action"
        );

        // Привязываем events и balancer
        $this->connection->getChannel()->queue_bind(
            $this->queueName,
            'amq.topic',
            "*.*.*.*.event"
        );
    }

    public function destructEnvironment(): void
    {
    }

    public function listenQueue(): void
    {
        RabbitMQBus::getConnection()->getChannel()->basic_consume(
            $this->queueName,
            '',
            true,
            true,
            false,
            false,
            fn(AMQPMessage $message) => $this->processMessage(json_decode($message->body, true))
        );

        while (true) {
            RabbitMQBus::getConnection()->getChannel()->wait();
        }
    }

    private function processMessage(array $body)
    {
        if (!isset($body['type'])) {
            throw new MessageProcessingException();
        }

        switch ($body['type']) {
            case MessageType::ACTION:
                $this->processActionMessage(ActionMessage::fromArray($body));
                break;
            case MessageType::EVENT:
                $this->processEventMessage(EventMessage::fromArray($body));
                throw new MessageProcessingException('НЕ РЕАЛИЗОВАНО!'); # TODO: Реализовать.
            default:
                throw new MessageProcessingException('Error processing queue message! ' . json_encode($body));
        }
    }

    public function consumeReplyMessage(ActionMessage $actionMessage, callable $callback): void
    {
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

        $this->connection->getChannel()->basic_consume(
            $actionMessage->getUuid(),
            '',
            true,
            true,
            false,
            false,
            fn(AMQPMessage $message) => $callback($convertJsonToMessage($message->body))
        );

        $this->connection->getChannel()->wait();
    }

    private function processActionMessage(ActionMessage $actionMessage)
    {
        try {
            $startProcessingMessage = new StartProcessingMessage();
            $startProcessingMessage->setActionMessage($actionMessage);
            Bus::getInstance()->publishMessage($startProcessingMessage);

            Session::setActionMessage($actionMessage);
            $actionResultMessage = new ActionResultMessage();
            $actionResultMessage->setActionMessage($actionMessage);
            $actionCaller = new ActionCaller(
                $actionMessage->getModelName(),
                $actionMessage->getActionName(),
                $actionMessage->getParameters()
            );
            $actionResultMessage->setData($actionCaller->call());
            Bus::getInstance()->publishMessage($actionResultMessage);
        } catch (Throwable $exception) {
            report($exception);
        }

        Session::unsetActionMessage();
    }

    private function processEventMessage(EventMessage $eventMessage)
    {
        try {
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
                $listener->{'handle'}($eventMessage->getData() ?? []);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

}
