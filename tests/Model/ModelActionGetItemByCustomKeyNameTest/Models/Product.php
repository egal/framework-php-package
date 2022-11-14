<?php

namespace Egal\Tests\Model\ModelActionGetItemByCustomKeyNameTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\ActionMetadataBlanks;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{

    public const TABLE = 'products';

    protected $table = self::TABLE;

    public $timestamps = false;

    public static function createSchema(): void
    {
        Schema::dropIfExists(self::TABLE);
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->string('key');
            $table->string('value');
        });
    }

    public static function dropSchema()
    {
        Schema::dropIfExists(self::TABLE);
    }

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
