<?php

namespace Egal\Core\Bus;

use Egal\Core\Exceptions\UnableDetermineMessageTypeException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\EventMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
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
        $processUuid = Str::uuid()->toString();
        $this->queueName = words_to_dot_case(config('app.service_name'), $processUuid, 'queue');
        $exchangeBalancerName = words_to_dot_case(config('app.service_name'), 'balancer', 'exchange');

        $this->connection->declareQueue(
            $this->queueName,
            false,
            true,
            ["x-queue-mode" => "default"]
        );
        $this->connection->declareExchange(
            $exchangeBalancerName,
            'x-consistent-hash',
            false,
            false,
            ['hash-header' => 'hash-on']
        );
        $this->connection->bindQueue($this->queueName, $exchangeBalancerName, '100');

        // Привязываем actions и balancer
        $this->connection->getChannel()->exchange_bind(
            $exchangeBalancerName,
            'amq.topic',
            words_to_dot_case(config('app.service_name'), '*', '*', 'action')
        );

        // Привязываем events и balancer
        $this->connection->getChannel()->exchange_bind(
            $exchangeBalancerName,
            'amq.topic',
            words_to_dot_case('*', '*', '*', '*', 'event')
        );
    }

    public function destructEnvironment(): void
    {
        $this->connection->deleteQueue($this->queueName);
    }

    public function listenQueue(): void
    {
        Artisan::call('rabbitmq:consume', [
            '--queue' => $this->queueName,
            '--prefetch-count' => 1, # TODO: Разобраться сколько надо prefetch-count по стандарту
            '--sleep' => (config('queue.connections.rabbitmq.options.consume.sleep')) / 1000,
            '--timeout' => 0,
        ]);
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

        RabbitMQBus::getConnection()->getChannel()->basic_consume(
            $actionMessage->getUuid(),
            '',
            true,
            false,
            false,
            false,
            fn($message) => $callback($convertJsonToMessage($message->body))
        );

        RabbitMQBus::getConnection()->getChannel()->wait();
    }

}
