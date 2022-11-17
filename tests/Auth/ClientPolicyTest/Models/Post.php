<?php

namespace Egal\Tests\Auth\ClientPolicyTest\Models;

use Egal\Model\Enums\VariableType;
use Egal\Model\Metadata\FieldMetadata;
use Egal\Model\Metadata\ModelMetadata;
use Egal\Model\Model;
use Egal\Tests\Auth\ClientPolicyTest\Policies\PostPolicy;

class Post extends Model
{

    public static function constructMetadata(): ModelMetadata
    {
        return ModelMetadata::make(
            static::class,
            FieldMetadata::make('id', VariableType::INTEGER)
        )->policy(PostPolicy::class);
    }

}
