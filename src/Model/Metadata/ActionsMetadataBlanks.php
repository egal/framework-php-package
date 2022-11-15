<?php

namespace Egal\Model\Metadata;

use Egal\Model\Enums\VariableType;

final class ActionsMetadataBlanks
{

    /**
     * @return ActionMetadata[]
     */
    public static function CRUD(VariableType $keyType): array
    {
        return [
            ActionMetadataBlanks::getMetadata(),
            ActionMetadataBlanks::getItem($keyType),
            ActionMetadataBlanks::getItems(),
            ActionMetadataBlanks::create(),
            ActionMetadataBlanks::update($keyType),
        ];
    }

}
