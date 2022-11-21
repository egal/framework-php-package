<?php

declare(strict_types=1);

namespace Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier;

use Egal\Model\Builder;
use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\UnsupportedFieldPatternInFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterValueTypeException;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Filter\FilterConditions\FilterConditionApplier;
use Egal\Model\Metadata\FieldMetadata;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\In;

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
            self::filterByMorphRelationField($matches, $builder, $value, $operator, $boolean);
        } elseif (preg_match('/^(\w+)\.(exists)\(\)$/', $condition->getField(), $matches)) {
            self::filterByExistsRelation($matches, $operator, $value, $builder, $boolean);
        } elseif (preg_match('/^(\w+)\.(\w+)$/', $condition->getField(), $matches)) {
            self::filterByRelationField($matches, $builder, $value, $operator, $boolean);
        } elseif (preg_match('/^(\w+)$/', $condition->getField(), $matches)) {
            self::filterByField($condition, $builder, $value, $operator, $boolean);
        } else {
            throw new UnsupportedFieldPatternInFilterConditionException();
        }
    }

    /**
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     * @throws \ReflectionException
     */
    protected static function filterByMorphRelationField(
        mixed $matches,
        Builder $builder,
        mixed $value,
        string $operator,
        string $boolean
    ): void {
        // For condition field like `morph_rel[first_type,second_type].field`.
        [$relation, $field, $types] = [$matches[1], $matches[3], explode(',', $matches[2])];
        $builder->getModel()->getModelMetadata()->relationExistOrFail($relation);
        foreach ($types as $type) {
            $relationModelMetadata = (new $type())->getModelMetadata();
            $relationModelMetadata->fieldExistOrFail($field);
            // TODO: выяснить предмет валидации - зачем, непонятно
            // $relationModelMetadata->validateFieldValueType($field, $value);
        }

        $clause = static function (Builder $query) use ($field, $operator, $value): void {
            $query->where($field, $operator, $value);
        };
        $builder->hasMorph(camel_case($relation), $types, '>=', 1, $boolean, $clause);
    }

    /**
     * @throws \Egal\Model\Exceptions\UnsupportedFilterConditionException
     * @throws \Egal\Model\Exceptions\UnsupportedFilterValueTypeException
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     */
    protected static function filterByExistsRelation(
        mixed $matches,
        string $operator,
        mixed $value,
        Builder $builder,
        string $boolean
    ): void {
        // For condition field like `rel.exists()`.
        [$relation, $function] = [$matches[1], $matches[2]];
        if ($operator !== '=') {
            throw new UnsupportedFilterConditionException();
        }
        if (!is_bool($value)) {
            throw UnsupportedFilterValueTypeException::make($relation . '_' . $function, 'boolean');
        }
        $builder->getModel()->getModelMetadata()->relationExistOrFail($relation);
        $builder->has(camel_case($relation), $value ? '>=' : '<', 1, $boolean);
    }

    /**
     * @throws \Egal\Model\Exceptions\RelationNotFoundException
     * @throws \ReflectionException
     */
    protected static function filterByRelationField(
        mixed $matches,
        Builder $builder,
        mixed $value,
        string $operator,
        string $boolean
    ): void {
        // For condition field like `rel.field`.
        [$relation, $field, $model] = [$matches[1], $matches[2], $builder->getModel()];
        $model->getModelMetadata()->relationExistOrFail($relation);
        $relationName = camel_case($relation);
        $relationModelMetadata = $model->$relationName()->getQuery()->getModel()->getModelMetadata();
        $relationModelMetadata->fieldExistOrFail($field);
        // TODO: выяснить предмет валидации - зачем, непонятно
        // $relationModelMetadata->validateFieldValueType($field, $value);
        $clause = static function (Builder $query) use ($field, $operator, $value): void {
            $query->where($field, $operator, $value);
        };
        $builder->has($relationName, '>=', 1, $boolean, $clause);
    }

    /**
     * @throws \Egal\Model\Exceptions\UnsupportedFilterValueTypeException
     * @throws \Egal\Model\Exceptions\FieldNotFoundException
     * @throws \ReflectionException
     */
    protected static function filterByField(
        FilterCondition $condition,
        Builder $builder,
        mixed $value,
        string $operator,
        string $boolean
    ): void {
        // For condition field like `field`.
        [$field, $modelMetadata] = [$condition->getField(), $builder->getModel()->getModelMetadata()];
        $modelMetadata->fieldExistOrFail($field);

        $fieldMetadata = array_filter(
            [...$modelMetadata->getFields(), ...$modelMetadata->getFakeFields(), $modelMetadata->getKey()],
            fn(FieldMetadata $value) => $value->getName() === $field
        );
        $fieldMetadata = reset($fieldMetadata);

        $rules = array_filter($fieldMetadata->getValidationRules(), function (string|object $rule) {
            if (!is_string($rule)) {
                if ($rule instanceof Enum || $rule instanceof In) return true;
                if (!method_exists($rule, '__toString')) return false;
                $rule = $rule->__toString();
            }

            return Str::startsWith($rule, WhiteListValidationRulesForFilterValidation::values());
        });

        $validator = Validator::make(['value' => $value], ['value' => $rules]);

        if ($validator->fails()) {
            throw UnsupportedFilterValueTypeException::make($field);
        }

        $builder->where($condition->getField(), $operator, $value, $boolean);
    }

    /**
     * @throws FilterException
     */
    private static function getSqlOperator(string $operator): string
    {
        return match ($operator) {
            self::EQUAL_OPERATOR => '=',
            self::NOT_EQUAL_OPERATOR => '!=',
            self::GREATER_THEN_OPERATOR => '>',
            self::LESS_THEN_OPERATOR => '<',
            self::GREATER_OR_EQUAL_OPERATOR => '>=',
            self::LESS_OR_EQUAL_OPERATOR => '<=',
            self::CONTAIN_OPERATOR, self::START_WITH_OPERATOR, self::END_WITH_OPERATOR => 'LIKE',
            self::NOT_CONTAIN_OPERATOR => 'NOT LIKE',
            self::EQUAL_IGNORE_CASE_OPERATOR,
            self::CONTAIN_IGNORE_CASE_OPERATOR,
            self::START_WITH_IGNORE_CASE_OPERATOR,
            self::END_WITH_IGNORE_CASE_OPERATOR => 'ILIKE',
            self::NOT_EQUAL_IGNORE_CASE_OPERATOR, self::NOT_CONTAIN_IGNORE_CASE_OPERATOR => 'NOT ILIKE',
            default => throw new FilterException('Incorrect operator!'),
        };
    }

    private static function getPreparedValue(string $operator, mixed $value): mixed
    {
        return match ($operator) {
            self::CONTAIN_OPERATOR, self::CONTAIN_IGNORE_CASE_OPERATOR, self::NOT_CONTAIN_OPERATOR,
            self::NOT_CONTAIN_IGNORE_CASE_OPERATOR => '%' . $value . '%',
            self::START_WITH_OPERATOR, self::START_WITH_IGNORE_CASE_OPERATOR => $value . '%',
            self::END_WITH_OPERATOR, self::END_WITH_IGNORE_CASE_OPERATOR => '%' . $value,
            default => $value,
        };
    }

}
