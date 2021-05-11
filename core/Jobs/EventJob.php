<?php

namespace Egal\Core\Jobs;

use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EventHandlingException;
use Egal\Core\Messages\EventMessage;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;

class EventJob extends Job
{

    /**
     * @param RabbitMQJob $rabbitMQJob
     * @param array $payload
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $payload)
    {
        $rabbitMQJob->delete();
        $eventMessage = EventMessage::fromArray($payload);
        $listeners = EventManager::getListeners(
            $eventMessage->getServiceName(),
            $eventMessage->getModelName(),
            $eventMessage->getName()
        );

        foreach ($listeners as $listener) {
            if (!class_exists($listener)) {
                throw new EventHandlingException();
            }
            (new $listener())->{'handle'}($payload['data']);
        }
    }

}
