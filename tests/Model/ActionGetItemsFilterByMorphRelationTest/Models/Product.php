<?php

declare(strict_types=1);

namespace Egal\Tests\Model\ActionGetItemsFilterByMorphRelationTest\Models;

use Egal\Model\Enums\RelationType;
use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\RelationMetadata;
use Egal\Model\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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
                FieldMetadata::make('sale', VariableType::INTEGER)->nullable(),
            ])
            ->addFields(FieldsMetadataBlanks::timestamps())
            ->addRelations([
                RelationMetadata::make('comment', Comment::class, RelationType::BELONGS_TO),
            ]);
    }

    public function comment(): MorphOne
    {
        return $this->morphOne(Comment::class, 'to');
    }

}
