<?php

namespace Egal\Tests\Model\ActionsWithSetRelationsTest\Models;

use Egal\Model\Enums\RelationType;
use Egal\Model\Enums\VariableType;
use Egal\Model\Exceptions\ValidateException;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\RelationMetadata;
use Egal\Model\Metadata\RelationSaverMetadata;
use Egal\Model\Model;
use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class Company extends Model
{

    public const TABLE = 'companies';

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
        )->addRelations([
            RelationMetadata::make(
                'employees',
                Employee::class,
                RelationType::HAS_MANY
            )->setSaver(
                RelationSaverMetadata::make(function (self $company, array $value) {
                    $company->employees()->saveMany(
                        Employee::query()->whereIn('id', $value)->get()
                    );
                })
                    ->addValueValidationRule('array')
                    ->addValueContentValidationRule('exists:employees,id')
            )
        ]);
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class);
    }

}
