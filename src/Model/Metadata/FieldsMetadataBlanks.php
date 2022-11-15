<?php

namespace Egal\Model\Metadata;

use Egal\Model\Enums\VariableType;

final class FieldsMetadataBlanks
{

    /**
     * @return FieldMetadata[]
     */
    public static function timestamps(): array
    {
        return [
            FieldMetadata::make('updated_at', VariableType::DATETIME)->nullable()->guarded(),
            FieldMetadata::make('created_at', VariableType::DATETIME)->nullable()->guarded(),
        ];
    }

}
