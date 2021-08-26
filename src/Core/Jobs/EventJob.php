<?php

declare(strict_types=1);

namespace Egal\Core\Jobs;

use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EventHandlingException;
use Egal\Core\Messages\EventMessage;
use Throwable;

class EventJob extends Job
{

    /**
     * @param mixed[] $payload
     * @throws \Illuminate\Contracts\Container\BindingResolutionException|\Exception|\Egal\Core\Exceptions\EventHandlingException
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $payload): void
    {
        $rabbitMQJob->delete();

        try {
            $eventMessage = EventMessage::fromArray($payload);
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
                $listener->{'handle'}($payload['data']);
            }
        } catch (Throwable $exception) {
            report($exception);
        }
    }

}
