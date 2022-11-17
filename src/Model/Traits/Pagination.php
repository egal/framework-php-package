<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

trait Pagination
{

    /**
     * Номер стандартной выводимой страницы при пагинации.
     */
    protected int $page = 1;

    /**
     * Стандартное значение максимального количества элементов на странице при пагинации.
     *
     * Предназначено для защиты от высоких нагрузок на систему.
     * При изменении данного параметра, вся ответственность по производительности лежи на разработчике.
     */
    protected int $maxPerPage = 100;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

}
