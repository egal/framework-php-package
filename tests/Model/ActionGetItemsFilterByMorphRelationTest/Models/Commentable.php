<?php

declare(strict_types=1);

namespace Egal\Tests\Model\ActionGetItemsFilterByMorphRelationTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;

class Commentable extends Model
{

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER),
        )
            ->addFields(FieldsMetadataBlanks::timestamps());
    }

}

