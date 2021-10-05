<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Illuminate\Events\Dispatcher;

class CentrifugoEventDispatcher extends Dispatcher
{

    /**
     * Fire an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     * @param bool $halt
     * @return array|null
     */
    public function dispatch($event, $payload = [], $halt = false): ?array
    {
        $dispatch = parent::dispatch($event, $payload, $halt);

        if (method_exists($event, 'publish')) {
            $event->publish();
        }

        return $dispatch;
    }

}
