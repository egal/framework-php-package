<?php

namespace Egal\Model\Traits;

use Egal\Core\Events\GlobalEvent;
use Egal\Model\Model;

/**
 * @package Egal\Model
 * @mixin Model
 */
trait HasEvents
{

    /**
     * Запустить событие пользовательской модели для данного события.
     *
     * @param string $event
     * @param string $method
     * @return mixed|null
     * @noinspection PhpUnused
     */
    protected function fireCustomModelEvent($event, $method)
    {
        $result = parent::fireCustomModelEvent($event, $method);
        if (
            isset($this->dispatchesEvents[$event])
            && is_subclass_of($this->dispatchesEvents[$event], GlobalEvent::class)
        ) {
            (new $this->dispatchesEvents[$event]($this))->publish();
        }
        return $result;
    }

}
