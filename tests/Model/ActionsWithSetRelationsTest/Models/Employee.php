<?php

namespace Egal\Tests\Model\ActionsWithSetRelationsTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Employee extends Model
{

    public const TABLE = 'employees';

    protected $table = self::TABLE;

    public $timestamps = false;

    public static function createSchema(): void
    {
        Schema::dropIfExists(self::TABLE);
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public static function dropSchema()
    {
        Schema::dropIfExists(self::TABLE);
    }

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER)
        );
    }

}
