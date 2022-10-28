<?php

namespace Egal\Tests\Model\ModelActionsSetRelationsTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CompanyEmployees extends Model
{

    public const TABLE = 'company_employee';

    protected $table = self::TABLE;

    public $timestamps = false;

    public static function createSchema(): void
    {
        Schema::dropIfExists(self::TABLE);
        Schema::create(self::TABLE, function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('company_id');
            $table->foreignId('employee_id');
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
        )->addFields([
            FieldMetadata::make("company_id", VariableType::INTEGER),
            FieldMetadata::make("employee_id", VariableType::INTEGER),
        ]);
    }

}
