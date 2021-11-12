<?php

declare(strict_types=1);

namespace Egal\Model\With;

use Egal\Model\Exceptions\UnsupportedAggregateFunctionException;
use Egal\Model\Filter\FilterPart;

class Relation
{

    public const AGGREGATE_PATTERN = '/^(\w+)\.(\w+)\((\w+)?\)$/';
    public const AGGREGATE_FUNCTION = [
        'avg',
        'count',
        'exists',
        'max',
        'min',
        'sum',
    ];

    private string $name;

    private FilterPart $filter;

    private string $aggregateFunction;

    private string $aggregateColumn = '*';

    public static function fromString(string $string): self
    {
        $relation = new self();
        preg_match(self::AGGREGATE_PATTERN, $string, $matches);
        $relation->setName($matches[1]);
        $relation->setAggregateFunction($matches[2]);
        $column = $matches[3] ?? '*';
        $relation->setAggregateColumn($column);

        return $relation;
    }

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

    public function setAggregateFunction(string $aggregateFunction): Relation
    {
        if (!in_array($aggregateFunction, self::AGGREGATE_FUNCTION)) {
            throw UnsupportedAggregateFunctionException::make($aggregateFunction);
        }

        $this->aggregateFunction = $aggregateFunction;

        return $this;
    }

    public function isAggregateFunctionExists(): bool
    {
        return isset($this->aggregateFunction);
    }

    public function getAggregateColumn(): string
    {
        return $this->aggregateColumn;
    }

    public function setAggregateColumn(string $aggregateColumn): Relation
    {
        $this->aggregateColumn = $aggregateColumn;

        return $this;
    }

    public function getAggregateFunction(): string
    {
        return $this->aggregateFunction;
    }

    public function getAggregateResultColumnName(): string
    {
        return $this->getAggregateColumn() !== '*'
            ? $this->getName() . '_' . $this->getAggregateFunction() . '_' . $this->getAggregateColumn()
            : $this->getName() . '_' . $this->getAggregateFunction();
    }

}
