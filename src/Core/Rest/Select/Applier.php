<?php

namespace Egal\Core\Rest\Select;

use Egal\Core\Exceptions\EmptySelectException;
use Egal\Core\Rest\Filter\Field;
use Egal\Core\Rest\Filter\RelationField;
use Illuminate\Database\Eloquent\Builder;

class Applier
{
    public static function apply(Builder $query, array $fieldObjects): Builder
    {
        if ($fieldObjects === []) {
            throw new EmptySelectException('Select fields not specified!', 400);
        }

        $selectRelationFields = [];
        $selectFields = [];
        foreach ($fieldObjects as $fieldObject) {
            if($fieldObject instanceof RelationField) {
                $relation = $fieldObject->getRelation();
                $fieldName = $fieldObject->getField();
                $selectRelationFields[$relation][] = $fieldName;

                $selectRelationFields[$relation] = array_merge($selectRelationFields[$relation], );
            } elseif ($fieldObject instanceof Field) {
                $selectFields[] = $fieldObject->getName();
            }
        }

        $query->select($selectFields);

        //TODO без указания ключей не работает
        foreach ($selectRelationFields as $relation => $fields) {
            $query->with($relation . ':' . implode(',', $fields));
        }

        return $query;
    }
}
