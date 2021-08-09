<?php

declare(strict_types=1);

namespace Egal\Model\Filter\FilterConditions;

use Egal\Model\Builder;
use Egal\Model\Exceptions\FilterException;
use Egal\Model\Filter\FilterCombiner;
use Egal\Model\Filter\FilterCondition;

class SimpleFilterConditionApplier extends FilterConditionApplier
{

    private const EQUAL_OPERATOR = 'eq';
    private const NOT_EQUAL_OPERATOR = 'ne';
    private const GREATER_THEN_OPERATOR = 'gt';
    private const LESS_THEN_OPERATOR = 'lt';
    private const GREATER_OR_EQUAL_OPERATOR = 'ge';
    private const LESS_OR_EQUAL_OPERATOR = 'le';
    private const CONTAIN_OPERATOR = 'co';
    private const START_WITH_OPERATOR = 'sw';
    private const END_WITH_OPERATOR = 'ew';
    private const NOT_CONTAIN_OPERATOR = 'nc';

    public static function apply(Builder &$builder, FilterCondition $condition, string $beforeOperator): void
    {
        $clause = $beforeOperator === FilterCombiner::AND
            ? 'where'
            : 'orWhere';

        if (str_contains($condition->getField(), '.')) {
            $fieldParts = explode('.', $condition->getField());

            if (count($fieldParts) < 2) {
                throw new FilterException('Field format is not correct!');
            }

            $field = $fieldParts[count($fieldParts) - 1];
            $relationName = $fieldParts[count($fieldParts) - 2];
            $builder->getModel()->getModelMetadata()->relationExistOrFail($relationName);
            $builder->{$clause . 'Has'}(
                camel_case($relationName),
                static function (Builder $query) use ($condition, $field): void {
                    $query->where($field, static::getSqlOperator($condition), static::getPreparedValue($condition));
                }
            );
        } else {
            $builder->$clause(
                $condition->getField(),
                static::getSqlOperator($condition),
                static::getPreparedValue($condition)
            );
        }
    }

    private static function getSqlOperator(FilterCondition $condition): string
    {
        switch ($condition->getOperator()) {
            case self::EQUAL_OPERATOR:
                return '=';
            case self::NOT_EQUAL_OPERATOR:
                return '!=';
            case self::GREATER_THEN_OPERATOR:
                return '>';
            case self::LESS_THEN_OPERATOR:
                return '<';
            case self::GREATER_OR_EQUAL_OPERATOR:
                return '>=';
            case self::LESS_OR_EQUAL_OPERATOR:
                return '<=';
            case self::CONTAIN_OPERATOR:
            case self::START_WITH_OPERATOR:
            case self::END_WITH_OPERATOR:
                return 'LIKE';
            case self::NOT_CONTAIN_OPERATOR:
                return 'NOT LIKE';
            default:
                throw new FilterException('Incorrect operator!');
        }
    }

    /**
     * @return mixed
     */
    private static function getPreparedValue(FilterCondition $condition)
    {
        switch ($condition->getOperator()) {
            case self::CONTAIN_OPERATOR:
            case self::NOT_CONTAIN_OPERATOR:
                return '%' . $condition->getValue() . '%';
            case self::START_WITH_OPERATOR:
                return $condition->getValue() . '%';
            case self::END_WITH_OPERATOR:
                return '%' . $condition->getValue();
            default:
                return $condition->getValue();
        }
    }

}
