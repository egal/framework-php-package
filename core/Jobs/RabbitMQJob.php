<?php

namespace Egal\Core\Jobs;

use Egal\Core\Exceptions\EventProcessingException;
use Egal\Core\Exceptions\QueueProcessingException;
use Egal\Core\Messages\MessageType;
use Exception;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseRabbitMQJob;

class RabbitMQJob extends BaseRabbitMQJob
{

    /**
     * @return array
     * @throws Exception
     */
    public function payload(): array
    {
        $payload = parent::payload();
        if (isset($payload['job'])) {
            return $payload;
        }

        if (!isset($payload['type'])) {
            throw new QueueProcessingException();
        }

        switch ($payload['type']) {
            case MessageType::ACTION:
                return [
                    'job' => ActionJob::class . '@handle',
                    'data' => $payload
                ];
            case MessageType::EVENT:
                if (!isset($payload['data'])) {
                    throw new EventProcessingException('Невозможно обработать Event! ' . json_encode($payload));
                }
                return [
                    'job' => EventJob::class . '@handle',
                    'data' => $payload
                ];
            default:
                throw new QueueProcessingException('Error processing queue message! ' . json_encode($payload));
        }
    }

}
