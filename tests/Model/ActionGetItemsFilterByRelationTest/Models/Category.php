<?php

namespace Egal\Tests\Model\ActionGetItemsFilterByRelationTest\Models;

use Egal\Model\Enums\VariableType as VariableT;
use Egal\Model\Metadata\FieldMetadata as FieldM;
use Egal\Model\Metadata\FieldsMetadataBlanks;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Metadata\ModelMetadata as ModelM;
use Egal\Model\Model;

class Category extends Model
{

    protected $table = 'categories';

    public static function constructMetadata(): ModelMetadata
    {
        return ModelM::make(static::class, FieldM::make('id', VariableT::INTEGER))
            ->addFields([
                FieldM::make('name', VariableT::STRING),
                FieldM::make('sale', VariableT::INTEGER),
            ])
            ->addFields(FieldsMetadataBlanks::timestamps());
    }

}
