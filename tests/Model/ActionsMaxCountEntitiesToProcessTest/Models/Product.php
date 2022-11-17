<?php

declare(strict_types=1);

namespace Egal\Tests\Model\ActionsMaxCountEntitiesToProcessTest\Models;

use Egal\Model\Enums\RelationType;
use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\RelationMetadata;
use Egal\Model\Model;

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
                FieldMetadata::make('name', VariableType::STRING),
            ]);
    }

}
