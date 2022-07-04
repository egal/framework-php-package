<?php

namespace Egal\Core\Rest\Filter;

class Query
{

    use Combinable;

    /**
     * @var array<int, Query|FieldCondition>
     */
    private array $conditions = [];

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function addCondition(Query|FieldCondition $condition): void
    {
        $this->conditions[] = $condition;
    }

    /**
     * @param array<int, Query|FieldCondition> $conditions
     */
    public static function make(array $conditions = [], ?Combiner $combiner = Combiner::And): static
    {
        $query = new static();
        $query->combiner = $combiner;
        $query->conditions = $conditions;

        return $query;
    }

    public function toArray(): array
    {
        $result = [];
        foreach ($this->conditions as $condition) {
            $result[] = [

            ];
        }
    }
}
