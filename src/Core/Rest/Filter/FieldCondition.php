<?php

namespace Egal\Core\Rest\Filter;

class FieldCondition
{

    use Combinable;

    private AbstractField $field;
    private Operator $operator;
    private null|bool|int|float|string $value;

    public static function make(
        AbstractField              $field,
        Operator                   $operator,
        null|bool|int|float|string $value,
        Combiner                   $combiner = Combiner::And
    ): static
    {
        $condition = new static();
        $condition->field = $field;
        $condition->operator = $operator;
        $condition->value = $value;
        $condition->combiner = $combiner;

        return $condition;
    }

    public function getField(): AbstractField
    {
        return $this->field;
    }

    public function getOperator(): Operator
    {
        return $this->operator;
    }

    public function getValue(?Operator $operator = null): float|bool|int|string|null
    {
        return match ($operator) {
            Operator::ContainOperator, Operator::ContainIgnoreCaseOperator, Operator::NotContainOperator, Operator::NotContainIgnoreCaseOperator => '%' . $this->value . '%',
            Operator::StartWithOperator, Operator::StartWithIgnoreCaseOperator => $this->value . '%',
            Operator::EndWithOperator, Operator::EqualIgnoreCaseOperator => '%' . $this->value,
            default => $this->value,
        };
    }

}
