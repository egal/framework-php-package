<?php

namespace Egal\Core\Handlers;

use Egal\Core\Events\EventManager;
use Egal\Core\Exceptions\EventHandlingException;
use Egal\Core\Messages\EventMessage;

class EventHandler implements HandlerInterface
{
    /**
     * @param array $data
     * @throws EventHandlingException
     */
    public function handle(array $data)
    {
        $eventMessage = EventMessage::fromArray($data);
        $listeners = EventManager::getListeners(
            $eventMessage->getServiceName(),
            $eventMessage->getModelName(),
            $eventMessage->getName()
        );

        foreach ($listeners as $listener) {
            if (!class_exists($listener)) {
                throw new EventHandlingException();
            }
            (new $listener())->{'handle'}($data['data']);
        }
    }
}
