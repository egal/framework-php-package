<?php

declare(strict_types=1);

namespace Egal\Model\Filter\FilterConditions;

use Egal\Model\Builder;
use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\UnsupportedFilterConditionFieldFormException;
use Egal\Model\Exceptions\UnsupportedFilterFieldException;
use Egal\Model\Exceptions\UnsupportedFilterValueException;
use Egal\Model\Filter\FilterCondition;
use Illuminate\Support\Facades\Validator;

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

    /**
     * @throws UnsupportedFilterFieldException
     * @throws UnsupportedFilterValueException
     * @throws FilterException
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     * @throws \ReflectionException
     * @throws UnsupportedFilterConditionFieldFormException
     */
    public static function apply(Builder &$builder, FilterCondition $condition, string $boolean): void
    {
        $operator = static::getSqlOperator($condition->getOperator());
        $value = static::getPreparedValue($condition->getOperator(), $condition->getValue());

        if (preg_match('/^(\w+)\[([\w,\\\\]+)\]\.(\w+)$/', $condition->getField(), $matches)) {
            // For condition field like `morph_rel[first_type,second_type].field`.
            $relation = $matches[1];
            $field = $matches[3];
            $types = explode(',', $matches[2]);
            self::validateMorphRelationFieldAndValue($builder, $relation, $types, $field, $value);
            $clause = static function (Builder $query) use ($field, $operator, $value): void {
                $query->where($field, $operator, $value);
            };
            $builder->hasMorph(camel_case($relation), $types, '>=', 1, $boolean, $clause);
        } elseif (preg_match('/^(\w+)\.(\w+)$/', $condition->getField(), $matches)) {
            // For condition field like `rel.field`.
            $relation = $matches[1];
            $field = $matches[2];
            self::validateRelationFieldAndValue($builder, $relation, $field, $value);
            $clause = static function (Builder $query) use ($field, $operator, $value): void {
                $query->where($field, $operator, $value);
            };
            $builder->has(camel_case($relation), '>=', 1, $boolean, $clause);
        } elseif (preg_match('/^(\w+)$/', $condition->getField(), $matches)) {
            // For condition field like `field`.
            $field = $condition->getField();
            $modelMetadata = $builder->getModel()->getModelMetadata();
            self::validateFieldByMetadata($field, $modelMetadata);
            self::validateFieldValueByMetadata($field, $value, $modelMetadata);
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

    /**
     * @throws \ReflectionException
     * @throws UnsupportedFilterFieldException
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     * @throws UnsupportedFilterValueException
     */
    private static function validateMorphRelationFieldAndValue(Builder $builder, string $relation, array $types, string $field, $value)
    {
        $builder->getModel()->getModelMetadata()->relationExistOrFail($relation);
        foreach ($types as $type) {
            $relationModelMetadata = (new $type)->getModelMetadata();
            self::validateFieldByMetadata($field, $relationModelMetadata);
            self::validateFieldValueByMetadata($field, $value, $relationModelMetadata);
        }
    }

    /**
     * @param string $field
     * @param string $value
     * @param $modelMetadata
     * @throws UnsupportedFilterValueException
     */
    private static function validateFieldValueByMetadata(string $field, $value, $modelMetadata): void
    {
        $validator = Validator::make(
            [$field => $value],
            [$field => $modelMetadata->getValidationRules($field)]
        );
        if ($validator->fails()) {
            throw new UnsupportedFilterValueException();
        }
    }

    /**
     * @param string $field
     * @param $modelMetadata
     * @throws UnsupportedFilterFieldException
     */
    private static function validateFieldByMetadata(string $field, $modelMetadata): void
    {
        if (!in_array($field, $modelMetadata->getFields())) {
            throw new UnsupportedFilterFieldException();
        }
    }

    /**
     * @throws \ReflectionException
     * @throws UnsupportedFilterFieldException
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     */
    private static function validateRelationFieldAndValue(Builder $builder, string $relation, string $field, $value)
    {
        $model = $builder->getModel();
        $model->getModelMetadata()->relationExistOrFail($relation);
        $relationName = camel_case($relation);
        $relationModelMetadata = $model->$relationName()->getQuery()->getModel()->getModelMetadata();
        self::validateFieldByMetadata($field, $relationModelMetadata);
        self::validateFieldValueByMetadata($field, $value, $relationModelMetadata);
    }

}
