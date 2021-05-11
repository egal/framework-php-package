<?php

namespace EgalFramework\Model\Traits;

use Closure;

trait HasEvents
{

    /**
     * Регистрация событие получения модели в диспетчере.
     *
     * @param  Closure|string  $callback
     * @return void
     */
    public static function got($callback)
    {
        static::registerModelEvent('got', $callback);
    }

}