<?php

declare(strict_types=1);

namespace Egal\Model\Filter\FilterConditions;

use Egal\Model\Builder;
use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\UnsupportedFilterConditionFieldFormException;
use Egal\Model\Filter\FilterCondition;

class SimpleFilterConditionApplier extends FilterConditionApplier
{

    private const EQUAL_OPERATOR = 'eq';
    private const EQUAL_IGNORE_CASE_OPERATOR = 'eqi';
    private const NOT_EQUAL_OPERATOR = 'ne';
    private const NOT_EQUAL_IGNORE_CASE_OPERATOR = 'nei';
    private const GREATER_THEN_OPERATOR = 'gt';
    private const GREATER_OR_EQUAL_OPERATOR = 'ge';
    private const LESS_THEN_OPERATOR = 'lt';
    private const LESS_OR_EQUAL_OPERATOR = 'le';
    private const CONTAIN_OPERATOR = 'co';
    private const CONTAIN_IGNORE_CASE_OPERATOR = 'coi';
    private const NOT_CONTAIN_OPERATOR = 'nc';
    private const NOT_CONTAIN_IGNORE_CASE_OPERATOR = 'nci';
    private const START_WITH_OPERATOR = 'sw';
    private const START_WITH_IGNORE_CASE_OPERATOR = 'swi';
    private const END_WITH_OPERATOR = 'ew';
    private const END_WITH_IGNORE_CASE_OPERATOR = 'ewi';

    public static function apply(Builder &$builder, FilterCondition $condition, string $boolean): void
    {
        $operator = static::getSqlOperator($condition->getOperator());
        $value = static::getPreparedValue($condition->getOperator(), $condition->getValue());

        if (preg_match('/^(\w+)\[([\w,\\\\]+)\]\.(\w+)$/', $condition->getField(), $matches)) {
            // For condition field like `morph_rel[first_type,second_type].field`.
            $relation = $matches[1];
            $field = $matches[3];
            $types = explode(',', $matches[2]);

            $builder->getModel()->getModelMetadata()->relationExistOrFail($relation);

            foreach ($types as $type) {
                $relationModelMetadata = (new $type())->getModelMetadata();
                $relationModelMetadata->fieldExistOrFail($field);
                $relationModelMetadata->validateFieldValueType($field, $value);
            }

            $clause = static function (Builder $query) use ($field, $operator, $value): void {
                $query->where($field, $operator, $value);
            };
            $builder->hasMorph(camel_case($relation), $types, '>=', 1, $boolean, $clause);
        } elseif (preg_match('/^(\w+)\.(\w+)$/', $condition->getField(), $matches)) {
            // For condition field like `rel.field`.
            $relation = $matches[1];
            $field = $matches[2];

            $model = $builder->getModel();
            $model->getModelMetadata()->relationExistOrFail($relation);
            $relationName = camel_case($relation);
            $relationModelMetadata = $model->$relationName()->getQuery()->getModel()->getModelMetadata();
            $relationModelMetadata->fieldExistOrFail($field);
            $relationModelMetadata->validateFieldValueType($field, $value);

            $clause = static function (Builder $query) use ($field, $operator, $value): void {
                $query->where($field, $operator, $value);
            };
            $builder->has(camel_case($relation), '>=', 1, $boolean, $clause);
        } elseif (preg_match('/^(\w+)$/', $condition->getField(), $matches)) {
            // For condition field like `field`.
            $field = $condition->getField();

            $modelMetadata = $builder->getModel()->getModelMetadata();
            $modelMetadata->fieldExistOrFail($field);
            $modelMetadata->validateFieldValueType($field, $value);

            $builder->where($condition->getField(), $operator, $value, $boolean);
        } else {
            throw new UnsupportedFilterConditionFieldFormException();
        }
    }

    private static function getSqlOperator(string $operator): string
    {
        switch ($operator) {
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
            case self::EQUAL_IGNORE_CASE_OPERATOR:
            case self::CONTAIN_IGNORE_CASE_OPERATOR:
            case self::START_WITH_IGNORE_CASE_OPERATOR:
            case self::END_WITH_IGNORE_CASE_OPERATOR:
                return 'ILIKE';
            case self::NOT_EQUAL_IGNORE_CASE_OPERATOR:
            case self::NOT_CONTAIN_IGNORE_CASE_OPERATOR:
                return 'NOT ILIKE';
            default:
                throw new FilterException('Incorrect operator!');
        }
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function getPreparedValue(string $operator, $value)
    {
        switch ($operator) {
            case self::CONTAIN_OPERATOR:
            case self::CONTAIN_IGNORE_CASE_OPERATOR:
            case self::NOT_CONTAIN_OPERATOR:
            case self::NOT_CONTAIN_IGNORE_CASE_OPERATOR:
                return '%' . $value . '%';
            case self::START_WITH_OPERATOR:
            case self::START_WITH_IGNORE_CASE_OPERATOR:
                return $value . '%';
            case self::END_WITH_OPERATOR:
            case self::END_WITH_IGNORE_CASE_OPERATOR:
                return '%' . $value;
            default:
                return $value;
        }
    }

}
