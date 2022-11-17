<?php

namespace Egal\Tests\Model\ActionGetItemsWithEmptyArrayPaginationParamsAndHiddenFieldsTest\Models;

use Egal\Model\Enums\RelationType;
use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\RelationMetadata;
use Egal\Model\Model;
use Egal\Tests\Model\ActionGetItemsFilterRelationsTest\Models\Category;

class Product extends Model
{

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER),
        )
            ->addFields([
                FieldMetadata::make('name', VariableType::STRING)->required(),
                FieldMetadata::make('count', VariableType::INTEGER)->required(),
                FieldMetadata::make('sale', VariableType::INTEGER)->hidden(),
            ])
            ->addFields(FieldsMetadataBlanks::timestamps())
            ->addRelations([
                RelationMetadata::make('category', Category::class, RelationType::BELONGS_TO)
            ]);
    }

}
