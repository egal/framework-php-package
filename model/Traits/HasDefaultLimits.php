<?php

namespace Egal\Model\Traits;

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
     * @return int
     */
    public function getMaxDisplayedCount(): int
    {
        return $this->maxDisplayedCount;
    }

}
