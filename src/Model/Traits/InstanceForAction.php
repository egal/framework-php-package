<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

/**
 * Trait InstanceForAction
 *
 * @package Egal\Model\Traits
 * @mixin \Egal\Model\Model
 */
trait InstanceForAction
{

    /**
     * Mark of the current instance as being used in actions.
     */
    protected bool $isInstanceForAction = false;

    /**
     * Mark the current instance as being used in actions.
     */
    protected function makeIsInstanceForAction(): self
    {
        $this->isInstanceForAction = true;

        return $this;
    }

    /**
     * Make new instance of current class as being used in actions.
     */
    protected static function newInstanceForAction(): self
    {
        $instance = new static();
        $instance->makeIsInstanceForAction();

        return $instance;
    }

}
