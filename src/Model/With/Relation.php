<?php

declare(strict_types=1);

namespace Egal\Model\With;

use Egal\Model\Filter\FilterPart;

class Relation
{

    private string $name;

    private FilterPart $filter;

    public function setName(string $name): Relation
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isFilterExists(): bool
    {
        return isset($this->filter);
    }

    public function getFilter(): FilterPart
    {
        return $this->filter;
    }

    public function setFilter(FilterPart $filter): Relation
    {
        $this->filter = $filter;

        return $this;
    }

}
