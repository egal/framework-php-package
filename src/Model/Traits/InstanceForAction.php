<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

/**
 * @mixin \Egal\Model\Model
 */
trait InstanceForAction
{

    protected bool $isInstanceForAction = false;

    public function makeIsInstanceForAction(): self
    {
        $this->isInstanceForAction = true;

        return $this;
    }

    public function newInstance($attributes = [], $exists = false): self
    {
        if (isset($this)) {
            $instance = parent::newInstance($attributes, $exists);
            if ($this->isInstanceForAction) {
                $instance->makeIsInstanceForAction();
            }
        } else {
            $instance = new static();
        }

        return $instance;
    }

    protected static function newInstanceForAction(): self
    {
        $instance = new static();
        $instance->makeIsInstanceForAction();

        return $instance;
    }

}
