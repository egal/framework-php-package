<?php

namespace Egal\Tests\Model\ActionGetItemByCustomKeyNameTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\ActionMetadataBlanks;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{

    public $timestamps = false;

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(static::class, FieldMetadata::make('key', VariableType::STRING))
            ->addFields([
                FieldMetadata::make('value', VariableType::STRING)
                    ->required()
            ])
            ->addActions([
                ActionMetadataBlanks::getItem(VariableType::STRING),
            ]);
    }

}
