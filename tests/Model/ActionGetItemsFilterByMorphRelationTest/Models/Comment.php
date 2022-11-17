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
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER),
        )
            ->addFields(FieldsMetadataBlanks::timestamps())
            ->addRelations([
                RelationMetadata::make('commentable', Commentable::class, RelationType::HAS_MANY),
            ]);
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

}
