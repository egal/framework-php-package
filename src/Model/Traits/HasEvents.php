<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

/**
 * @package Egal\Model
 * @mixin \Egal\Model\Model
 */
trait HasEvents
{

    private bool $needFireActionEvents = false;

    /**
     * Create a new instance of the given model.
     *
     * @param array $attributes
     * @param bool $exists
     * @return static
     */
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

    public function isNeedFireActionEvents(): bool
    {
        return $this->needFireActionEvents;
    }

    public static function retrievedWithAction($callback): void
    {
        static::registerModelEvent('retrieved.action', $callback);
    }

    public static function creatingWithAction($callback): void
    {
        static::registerModelEvent('creating.action', $callback);
    }

    public static function createdWithAction($callback): void
    {
        static::registerModelEvent('created.action', $callback);
    }

    public static function updatingWithAction($callback): void
    {
        static::registerModelEvent('updating.action', $callback);
    }

    public static function updatedWithAction($callback): void
    {
        static::registerModelEvent('updated.action', $callback);
    }

    public static function savingWithAction($callback): void
    {
        static::registerModelEvent('saving.action', $callback);
    }

    public static function savedWithAction($callback): void
    {
        static::registerModelEvent('saved.action', $callback);
    }

    public static function deletingWithAction($callback): void
    {
        static::registerModelEvent('deleting.action', $callback);
    }

    public static function deletedWithAction($callback): void
    {
        static::registerModelEvent('deleted.action', $callback);
    }

    /**
     * Запустить событие пользовательской модели для данного события.
     *
     * @return mixed|null
     * @noinspection PhpUnused
     */
    protected function fireCustomModelEvent(string $event, string $method)
    {
        $result = parent::fireCustomModelEvent($event, $method);

        if (isset($this->dispatchesEvents[$event]) && method_exists($this->dispatchesEvents[$event], 'publish')) {
            (new $this->dispatchesEvents[$event]($this))->publish();
        }

        return $result;
    }

    protected function fireModelEvent($event, $halt = true): void
    {
        if ($this->isNeedFireActionEvents()) {
            $this->fireActionEvent($event, $halt);
        }

        parent::fireModelEvent($event, $halt);
    }

    protected function fireActionEvent($event, $halt = true): void
    {
        parent::fireModelEvent($event . '.action', $halt);
    }

}
