<?php

namespace Egal\Model\Traits;

use Egal\Model\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait UsesUuidKey
{

    protected static function bootUsesUuidKey()
    {
        static::creating(function ($model) {
            /** @var Model $model */
            $model->setAttribute($model->getKeyName(), (string)Str::uuid());
        });
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

}
