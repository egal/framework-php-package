<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Core\Events\GlobalEvent;

/**
 * @mixin \Egal\Model\Model
 */
trait HasEvents
{

    /**
     * @param  string  $event
     * @param  string  $method
     * @return mixed|null
     */
    protected function fireCustomModelEvent($event, $method)
    {
        $result = parent::fireCustomModelEvent($event, $method);

        $dispatchesEvent = $this->dispatchesEvents[$event] ?? null;

        if (isset($dispatchesEvent) && is_subclass_of($dispatchesEvent, GlobalEvent::class)) {
            (new $dispatchesEvent($this))->publish();
        }

        return $result;
    }

}
