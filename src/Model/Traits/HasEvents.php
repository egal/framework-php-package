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

    private bool $needFireActionEvents = false;

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

    public function newInstance($attributes = [], $exists = false)
    {
        $instance = parent::newInstance($attributes, $exists);
        if ($this->isNeedFireActionEvents()) {
            $instance->needFireActionEvents();
        }
        return $instance;
    }

    public function needFireActionEvents(): self
    {
        $this->needFireActionEvents = true;
        return $this;
    }

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

    protected function fireModelEvent($event, $halt = true)
    {
        if ($this->isNeedFireActionEvents()) {
            $this->fireActionEvent($event, $halt);
        }
        parent::fireModelEvent($event, $halt);
    }

    public function isNeedFireActionEvents(): bool
    {
        return $this->needFireActionEvents;
    }

    protected function fireActionEvent($event, $halt = true)
    {
        parent::fireModelEvent($event . '.action', $halt);
    }

}
