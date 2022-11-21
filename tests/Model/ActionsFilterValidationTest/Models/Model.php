<?php

declare(strict_types=1);

namespace Egal\Tests\Model\ActionsFilterValidationTest\Models;

use Egal\Model\Builder;
use Egal\Model\Enums\VariableType;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model as BaseModel;

class Model extends BaseModel
{

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER),
        )
            ->addFields([
                FieldMetadata::make('string', VariableType::STRING),
                FieldMetadata::make('unique_string', VariableType::STRING)
                    ->addValidationRule('unique:models,unique_string'),
                FieldMetadata::make('integer', VariableType::INTEGER),
                FieldMetadata::make('numeric', VariableType::NUMERIC),
                FieldMetadata::make('boolean', VariableType::BOOLEAN),
                FieldMetadata::make('array', VariableType::JSON), // TODO: Type to ARRAY.
                FieldMetadata::make('json', VariableType::JSON),
                FieldMetadata::make('fake', VariableType::JSON),
                FieldMetadata::make('date', VariableType::DATE),
                FieldMetadata::make('datetime', VariableType::DATETIME),
            ])
            ->addFields(FieldsMetadataBlanks::timestamps())
            ->addFakeFields([
                FieldMetadata::make('fake', VariableType::JSON),
            ])
            ->addCasts([
                'array' => 'array',
            ]);
    }

    public static function applyFooFilterCondition(
        Builder         &$builder,
        FilterCondition $condition,
        string          $beforeOperator
    ): void
    {

    }

}
