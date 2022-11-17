<?php

namespace Egal\Tests\Model\ActionGetItemsFilterRelationsTest\Models;

use Egal\Model\Enums\RelationType;
use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\RelationMetadata;
use Egal\Model\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{

    public $timestamps = false;

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER),
        )
            ->addFields([
                FieldMetadata::make('category_id', VariableType::INTEGER)->required(),
            ])
            ->addRelations([
                RelationMetadata::make('category', Category::class, RelationType::BELONGS_TO)
            ]);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

}
