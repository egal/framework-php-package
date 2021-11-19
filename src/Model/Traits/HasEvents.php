<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Core\Events\GlobalEvent;

/**
 * @mixin \Egal\Model\Model
 */
trait HasEvents
{

    public static function retrievedWithAction($callback)
    {
        static::registerModelEvent('retrieved.action', $callback);
    }

    public static function creatingWithAction($callback)
    {
        static::registerModelEvent('creating.action', $callback);
    }

    public static function createdWithAction($callback)
    {
        static::registerModelEvent('created.action', $callback);
    }

    public static function updatingWithAction($callback)
    {
        static::registerModelEvent('updating.action', $callback);
    }

    public static function updatedWithAction($callback)
    {
        static::registerModelEvent('updated.action', $callback);
    }

    public static function savingWithAction($callback)
    {
        static::registerModelEvent('saving.action', $callback);
    }

    public static function savedWithAction($callback)
    {
        static::registerModelEvent('saved.action', $callback);
    }

    public static function deletingWithAction($callback)
    {
        static::registerModelEvent('deleting.action', $callback);
    }

    public static function deletedWithAction($callback)
    {
        static::registerModelEvent('deleted.action', $callback);
    }

    /**
     * @param string $event
     * @param string $method
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

    protected function fireModelEvent($event, $halt = true)
    {
        if ($this->isInstanceForAction) {
            $this->fireActionEvent($event, $halt);
        }

        parent::fireModelEvent($event, $halt);
    }

    protected function fireActionEvent($event, $halt = true)
    {
        parent::fireModelEvent($event . '.action', $halt);
    }

}
