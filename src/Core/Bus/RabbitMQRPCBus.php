<?php

namespace Egal\Core\Bus;

use Egal\Core\Exceptions\UnsupportedMessageTypeException;
use Egal\Core\Messages\ActionErrorMessage;
use Egal\Core\Messages\ActionMessage;
use Egal\Core\Messages\ActionResultMessage;
use Egal\Core\Messages\EventMessage;
use Egal\Core\Messages\Message;
use Egal\Core\Messages\MessageType;
use Egal\Core\Messages\StartProcessingMessage;
use Egal\Core\Communication\Request;
use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Connectors\RabbitMQConnector;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\RabbitMQQueue;

class RabbitMQRPCBus extends Bus
{

//    private RabbitMQConnector $connector;
    private AMQPStreamConnection $connection;
    public string $queueName;

    /**
     * RabbitMQBus constructor.
     * @throws Exception
     */
    public function __construct()
    {
//        $this->connector = new RabbitMQConnector(app('events'));
//        $this->connection = $this->connector->connect(config('queue.connections.rabbitmq'));

        $config = config('queue.connections.rabbitmq')['hosts'][0];
        $this->connection = new AMQPStreamConnection(
            $config['host'],
            $config['port'],
            $config['user'],
            $config['password'],
//            'localhost', 5672, 'guest', 'guest'
        );
    }

    /**
     * @return RabbitMQQueue
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private static function getConnection(): RabbitMQQueue
    {
        return (app(Bus::class))->connection;
    }

    /**
     * @param Message $message
     * @throws Exception
     */
    public function publishMessage(Message $message): void
    {
//        if (
//            !($routingKey = $this->getRoutingKey($message))
//            || !($exchange = $this->getExchange($message))
//        ) {
//            throw  new Exception('Невозможно опубликовать ' . get_class($message));
//        }
//
//        /* Additional actions before publish */
//        switch ($message->getType()) {
//            case MessageType::ACTION_ERROR:
//            case MessageType::ACTION_RESULT:
//            case MessageType::START_PROCESSING:
//                /** @var ActionErrorMessage|ActionResultMessage|StartProcessingMessage $message */
//                $this->connection->getChannel()->queue_declare(
//                    $message->getActionMessage()->getUuid(),
//                    true
//                );
//                $this->connection->getChannel()->queue_bind(
//                    $message->getActionMessage()->getUuid(),
//                    'amq.direct',
//                    $message->getActionMessage()->getUuid()
//                );
//                break;
//            case MessageType::ACTION:
//                /** @var ActionMessage $message */
//                $this->connection->getChannel()->queue_declare($message->getUuid());
//                break;
//        }
//
//        $AMQPMessage = new AMQPMessage(
//            $message->toJson(),
//            [
//                'delivery_mode' => 2,
//                'application_headers' => new AMQPTable(['hash-on' => $message->getUuid()])
//            ]
//        );
//
//        $this->connection->getChannel()->basic_publish($AMQPMessage, $exchange, $routingKey);
    }

    /**
     * @param Message $message
     * @return string
     * @throws Exception
     */
    protected function getExchange(Message $message): string
    {
//        switch ($message->getType()) {
//            case MessageType::ACTION:
//            case MessageType::EVENT:
//                return 'amq.topic';
//            case MessageType::ACTION_RESULT:
//            case MessageType::ACTION_ERROR:
//            case MessageType::START_PROCESSING:
//                return 'amq.direct';
//            default:
//                throw new UnsupportedMessageTypeException();
//        }
    }

    /**
     * @throws Exception
     */
    protected function getRoutingKey(Message $message): string
    {
//        switch ($message->getType()) {
//            case MessageType::ACTION:
//                /** @var ActionMessage $message */
//                return words_to_dot_case(
//                    $message->getServiceName(),
//                    $message->getModelName(),
//                    $message->getActionName(),
//                    $message->getType()
//                );
//            case MessageType::EVENT:
//                /** @var EventMessage $message */
//                return words_to_dot_case(
//                    $message->getServiceName(),
//                    $message->getModelName(),
//                    $message->getId(),
//                    $message->getName(),
//                    $message->getType()
//                );
//            case MessageType::ACTION_RESULT:
//            case MessageType::ACTION_ERROR:
//            case MessageType::START_PROCESSING:
//                /** @var ActionResultMessage|ActionErrorMessage|StartProcessingMessage $message */
//                return $message->getActionMessage()->getUuid();
//            default:
//                throw new UnsupportedMessageTypeException();
//        }
    }

    public function constructEnvironment(): void
    {
//        $processUuid = Str::uuid()->toString();
//        $this->queueName = words_to_dot_case(config('app.service_name'), $processUuid, 'queue');
//        $exchangeBalancerName = words_to_dot_case(config('app.service_name'), 'balancer', 'exchange');
//
//        $this->connection->declareQueue($this->queueName, true, true);
//        $this->connection->declareExchange(
//            $exchangeBalancerName,
//            'x-consistent-hash',
//            true,
//            false,
//            ['hash-header' => 'hash-on']
//        );
//        $this->connection->bindQueue($this->queueName, $exchangeBalancerName, '100');
//
//        // Привязываем actions и balancer
//        $this->connection->getChannel()->exchange_bind(
//            $exchangeBalancerName,
//            'amq.topic',
//            words_to_dot_case(config('app.service_name'), '*', '*', 'action')
//        );
//
//        // Привязываем events и balancer
//        $this->connection->getChannel()->exchange_bind(
//            $exchangeBalancerName,
//            'amq.topic',
//            words_to_dot_case('*', '*', '*', '*', 'event')
//        );
    }

    /**
     * @throws AMQPProtocolChannelException
     */
    public function destructEnvironment(): void
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function listenQueue(): void
    {

        $this->channel = $this->connection->channel();

        $this->channel->queue_declare('rpc_queue', false, false, false, false);

        function fib($n)
        {
            if ($n == 0) {
                return 0;
            }
            if ($n == 1) {
                return 1;
            }
            return fib($n-1) + fib($n-2);
        }

        $callback = function ($req) {
            $n = intval($req->body);

            $msg = new AMQPMessage(
                (string) fib($n),
                array('correlation_id' => $req->get('correlation_id'))
            );

            $req->delivery_info['channel']->basic_publish(
                $msg,
                '',
                $req->get('reply_to')
            );
            $req->ack();
        };

        $this->channel->basic_qos(null, 1, null);
        $this->channel->basic_consume('rpc_queue', '', false, false, false, false, $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

}
