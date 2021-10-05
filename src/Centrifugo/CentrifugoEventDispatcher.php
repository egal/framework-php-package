<?php

namespace Egal\Centrifugo;

use Illuminate\Events\Dispatcher;

class CentrifugoEventDispatcher extends Dispatcher
{
    public function dispatch($event, $payload = [], $halt = false)
    {
        $dispatch = parent::dispatch($event, $payload, $halt);
        if (method_exists($event, 'publish')) {
            $event->publish();
        }
        return $dispatch;
    }
}
