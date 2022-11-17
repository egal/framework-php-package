<?php

namespace Egal\Tests\Model\BootEventsResultsTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Company extends Model
{

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();
        static::saving(fn(self $model) => $model->setAttribute('name', 'NamedModel'));
    }

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER)
        )
            ->addFields([
                FieldMetadata::make('name', VariableType::STRING)->nullable(),
            ]);
    }

}
