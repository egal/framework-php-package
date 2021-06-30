<?php

namespace Egal\Model\Traits;

use Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException;

trait HasDefaultLimits
{

    /**
     * Стандартное значение максимального выводимого количества элементов.
     *
     * Предназначено для защиты от высоких нагрузок на систему.
     * При изменении данного параметра, вся ответственность по производительности лежи на разработчике.
     */
    protected int $maxDisplayedCount = 100;

    /**
     * The maximum count of entities that can be manipulated using actions
     *
     * Предназначено для защиты от высоких нагрузок на систему.
     * При изменении данного параметра, вся ответственность по производительности лежи на разработчике.
     */
    protected int $maxCountEntitiesCanToManipulateWithAction = 10;

    /**
     * @return int
     */
    public function getMaxDisplayedCount(): int
    {
        return $this->maxDisplayedCount;
    }

    /**
     * @return int
     */
    public function getMaxCountEntitiesCanToManipulateWithAction(): int
    {
        return $this->maxCountEntitiesCanToManipulateWithAction;
    }

    public function isLessThanMaxCountEntitiesCanToManipulateWithAction(int $count): bool
    {
        return $count < $this->getMaxCountEntitiesCanToManipulateWithAction();
    }

    public function isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(int $count): bool
    {
        if (!$this->isLessThanMaxCountEntitiesCanToManipulateWithAction($count)) {
            throw new ExceedingTheLimitCountEntitiesForManipulationException();
        }

        return true;
    }

}
