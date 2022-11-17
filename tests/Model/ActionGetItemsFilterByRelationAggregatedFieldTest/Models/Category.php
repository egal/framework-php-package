<?php

namespace Egal\Tests\Model\ActionGetItemsFilterByRelationAggregatedFieldTest\Models;

use Egal\Model\Enums\RelationType as RelationT;
use Egal\Model\Enums\VariableType as VariableT;
use Egal\Model\Metadata\FieldMetadata as FieldM;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\ModelMetadata as ModelM;
use Egal\Model\Metadata\RelationMetadata as RelationM;
use Egal\Model\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{

    public static function constructMetadata(): ModelMetadata
    {
        return ModelM::make(static::class, FieldM::make('id', VariableT::INTEGER))
            ->addFields(FieldsMetadataBlanks::timestamps())
            ->addRelations([
                RelationM::make('products', Product::class, RelationT::HAS_MANY),
            ]);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

}
