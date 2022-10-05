<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException;

/**
 * Trait HasDefaultLimits.
 *
 * Содержит в себе ограничения при использовании actions у {@see \Egal\Model\Model}.
 */
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
    protected int $maxCountEntitiesToProcess = 10;

    /**
     * Getter for {@see \Egal\Model\Traits\HasDefaultLimits::maxDisplayedCount}.
     */
    public function getMaxDisplayedCount(): int
    {
        return $this->maxDisplayedCount;
    }

    /**
     * Getter for {@see \Egal\Model\Traits\HasDefaultLimits::maxCountEntitiesToProcess}.
     */
    public function getMaxCountEntitiesToProcess(): int
    {
        return $this->maxCountEntitiesToProcess;
    }

    /**
     * Checks if {@param $count} is less than {@see \Egal\Model\Traits\HasDefaultLimits::maxCountEntitiesToProcess}.
     */
    public function isLessOrEqualMaxCountEntitiesCanToManipulateWithAction(int $count): bool
    {
        return $count <= $this->getMaxCountEntitiesToProcess();
    }

    /**
     * Checks if {@param $count} is less than {@see \Egal\Model\Traits\HasDefaultLimits::maxCountEntitiesToProcess}.
     *
     * Otherwise, throws an exception.
     *
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException
     */
    public function isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(int $count): bool
    {
        if (!$this->isLessOrEqualMaxCountEntitiesCanToManipulateWithAction($count)) {
            throw new ExceedingTheLimitCountEntitiesForManipulationException();
        }

        return true;
    }

}
