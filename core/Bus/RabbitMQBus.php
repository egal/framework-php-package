<?php

namespace Egal\Core\Bus;

use Egal\Core\Exceptions\EventProcessingException;
use Egal\Core\Exceptions\QueueProcessingException;
use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Handlers\ActionHandler;
use Egal\Core\Handlers\EventHandler;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\EventMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Exception;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPLazyConnection;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Channel\AMQPChannel;
use Throwable;

class RabbitMQBus extends Bus
{
    private AbstractConnection $connection;
    private AMQPChannel $channel;

    private string $queueName;
    public int $prefetchSize = 0;
    public int $prefetchCount = 1;

    /**
     * RabbitMQBus constructor.
     * @throws Exception
     */
    public function __construct()
    {
        $this->connect();
        $this->channel = $this->connection->channel();
    }

    public function connect()
    {
        $host = config('bus.connections.rabbitmq.host');
        $connectionClass = config('bus.connections.rabbitmq.connection');

        if (!$host) {
            throw new Exception('RabbitMQ configuration error.');
        }

        if (!$connectionClass || !($connectionClass instanceof AbstractConnection)) {
            $connectionClass = AMQPLazyConnection::class;
        }

        $this->connection = new $connectionClass(
            $host['host'],
            $host['port'],
            $host['user'],
            $host['password']
        );
    }

    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param Message $message
     * @throws Exception
     */
    public function publishMessage(Message $message): void
    {
        $routingKey = $this->getRoutingKey($message);
        $exchange = $this->getExchange($message);

        if (!$routingKey || !$exchange) {
            throw new Exception('Unable to publish ' . get_class($message));
        }

        /* Additional actions before publish */
        switch ($message->getType()) {
            case MessageType::ACTION_ERROR:
            case MessageType::ACTION_RESULT:
            case MessageType::START_PROCESSING:
                /** @var ActionErrorMessage|ActionResultMessage|StartProcessingMessage $message */
                $this->channel->queue_declare(
                    $message->getActionMessage()->getUuid(),
                    true
                );
                $this->channel->queue_bind(
                    $message->getActionMessage()->getUuid(),
                    'amq.direct',
                    $message->getActionMessage()->getUuid()
                );
                break;
            case MessageType::ACTION:
                /** @var ActionMessage $message */
                $this->channel->queue_declare($message->getUuid());
                break;
        }

        $AMQPMessage = new AMQPMessage(
            $message->toJson(),
            [
                'delivery_mode' => 2,
                'application_headers' => new AMQPTable(['hash-on' => $message->getUuid()])
            ]
        );

        $this->channel->basic_publish($AMQPMessage, $exchange, $routingKey);
    }

    /**
     * @param Message $message
     * @return string
     * @throws Exception
     */
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

    /**
     * @param Message $message
     * @return string
     * @throws UnsupportedMessageTypeException
     */
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
        $arguments = [];

        $this->channel->queue_declare(
            $this->queueName,
            false,
            true,
            false,
            true,
            false,
            new AMQPTable($arguments)
        );

        $this->channel->exchange_declare(
            $exchangeBalancerName,
            'x-consistent-hash',
            false,
            true,
            false,
            false,
            true,
            new AMQPTable(['hash-header' => 'hash-on'])
        );

        $this->channel->queue_bind($this->queueName, $exchangeBalancerName, '100');

        // Bind actions and balancer
        $this->channel->exchange_bind(
            $exchangeBalancerName,
            'amq.topic',
            words_to_dot_case(config('app.service_name'), '*', '*', 'action')
        );

        // Bind events and balancer
        $this->channel->exchange_bind(
            $exchangeBalancerName,
            'amq.topic',
            words_to_dot_case('*', '*', '*', '*', 'event')
        );
    }

    /**
     * @throws Exception
     */
    public function destructEnvironment(): void
    {
        $this->channel->queue_delete($this->queueName, true, true);
        $this->channel->close();
        $this->connection->close();
    }

    public function listenQueue(): void
    {
        $this->channel->basic_qos(
            $this->prefetchSize,
            $this->prefetchCount,
            null
        );

        $this->channel->basic_consume(
            $this->queueName,
            $this->consumerTag(),
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) {
                $this->processMessage($message);
            }
        );

        while ($this->channel->is_consuming()) {
            try {
                $this->channel->wait(null, true);
            } catch (AMQPConnectionClosedException $exception) {
                $this->connection->reconnect();
                throw $exception;
            } catch (AMQPRuntimeException $exception) {
                throw $exception;
            } catch (Exception | Throwable $exception) {
                throw $exception;
            }

            usleep(150000);
        }
    }

    protected function consumerTag(): string
    {
        return Str::slug(config('app.name', 'egal'), '_') . '_' . getmypid();
    }

    /**
     * @param AMQPMessage $message
     * @throws EventProcessingException
     * @throws QueueProcessingException
     * @throws \Egal\Auth\Exceptions\InitializeServiceServiceTokenException
     * @throws \Egal\Auth\Exceptions\InitializeUserServiceTokenException
     * @throws \Egal\Auth\Exceptions\TokenExpiredException
     * @throws \Egal\Auth\Exceptions\UndefinedTokenTypeException
     * @throws \Egal\Core\Exceptions\ActionCallException
     * @throws \Egal\Core\Exceptions\EventHandlingException
     * @throws \Egal\Core\Exceptions\InitializeMessageFromArrayException
     * @throws \Egal\Core\Exceptions\TokenSignatureInvalidException
     * @throws \Egal\Core\Exceptions\UndefinedTypeOfMessageException
     * @throws \ReflectionException
     */
    protected function processMessage(AMQPMessage $message)
    {
        $payload = json_decode($message->getBody(), true);

        if (!isset($payload['type'])) {
            throw new QueueProcessingException();
        }

        switch ($payload['type']) {
            case MessageType::ACTION:
                (new ActionHandler())->handle($payload);
                break;
            case MessageType::EVENT:
                if (!isset($payload['data'])) {
                    throw new EventProcessingException('Error processing event! ' . json_encode($payload));
                }
                (new EventHandler())->handle($payload);
                break;
            default:
                throw new QueueProcessingException('Error processing queue message! ' . json_encode($payload));
        }

        $message->ack();
    }
}
