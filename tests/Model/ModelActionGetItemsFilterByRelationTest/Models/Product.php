<?php

namespace Egal\Tests\Model\ModelActionGetItemsFilterByRelationTest\Models;

use Egal\Model\Enums\RelationType as RelationT;
use Egal\Model\Enums\VariableType as VariableT;
use Egal\Model\Metadata\FieldMetadata as FieldM;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata as ModelM;
use Egal\Model\Metadata\RelationMetadata as RelationM;
use Egal\Model\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{

    protected $table = 'products';

    public static function constructMetadata(): ModelM
    {
        return ModelM::make(static::class, FieldM::make('id', VariableT::INTEGER))
            ->addFields([
                FieldM::make('name', VariableT::STRING),
                FieldM::make('category_id', VariableT::INTEGER),
            ])
            ->addFields(FieldsMetadataBlanks::timestamps())
            ->addRelations([
                RelationM::make('category', Category::class, RelationT::BELONGS_TO),
                RelationM::make('category_with_word', Category::class, RelationT::BELONGS_TO),
            ]);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categoryWithWord(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

}
