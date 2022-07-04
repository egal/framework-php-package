<?php

namespace Egal\Core\Rest\Filter;

use Egal\Core\Database\Metadata\Model;
use Egal\Core\Exceptions\FilterApplyException;
use Illuminate\Database\Eloquent\Builder;

class Applier
{

    public static function applyQuery(Builder $query, Query $filterQuery): Builder
    {
        return $query->where(function($query) use ($filterQuery) {
            $filterConditions = $filterQuery->getConditions();

            foreach ($filterConditions as $condition) {
                self::applyCondition($query, $condition);
            }
            return $query;
        });
    }

    private static function applyCondition(Builder $query, Query|FieldCondition $condition)
    {
        if ($condition instanceof Query) {
            self::applyQuery($query, $condition);
        } else {
            $field = $condition->getField();
            $operator = $condition->getOperator();
            $sqlOperator = $operator->getSqlOperator();
            $value = $condition->getValue($operator);
            $combiner = $condition->getCombiner()->value;

            switch (true) {
                case $field instanceof MorphRelationField:
                    $relation = $field->getRelation();
                    $types = $field->getTypes();
                    $fieldName = $field->getField();
                    $clause = static function ($query) use ($fieldName, $sqlOperator, $value): void {
                        $query->where($fieldName, $sqlOperator, $value);
                    };
                    $query->hasMorph($relation, $types, '>=', 1, $combiner, $clause);
                    break;
                case $field instanceof RelationField:
                    $relation = $field->getRelation();
                    $fieldName = $field->getField();
                    $clause = static function ($query) use ($fieldName, $sqlOperator, $value): void {
                        $query->where($fieldName, $sqlOperator, $value);
                    };
                    $query->has($relation, '>=', 1, $combiner, $clause);
                    break;
                case $field instanceof Field:
                    $fieldName = $field->getName();
                    $query->where($fieldName, $sqlOperator, $value, $combiner);
                    break;
                default:
                    throw new FilterApplyException();
            }
        }
    }

    public static function validateQuery(Model $modelMetadata, Query $filterQuery)
    {
        $filterConditions = $filterQuery->getConditions();

        foreach ($filterConditions as $condition) {
            self::validateCondition($modelMetadata, $condition);
        }
    }

    private static function validateCondition(Model $modelMetadata, Query|FieldCondition $condition)
    {
//        if ($condition instanceof Query) {
//            self::validateQuery($modelMetadata, $condition);
//        } else {
//
//        }
    }

}
