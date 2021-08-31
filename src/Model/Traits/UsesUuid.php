<?php

namespace Egal\Model\Traits;

use Egal\Model\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 */
trait UsesUuid
{

    /**
     * @noinspection PhpUnused
     */
    protected static function bootUsesUuid()
    {
        static::creating(function ($model) {
            /** @var Model $model */
            $model->{$model->getKeyName()} = (string)Str::uuid();
        });
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function getIncrementing(): bool
    {
        return false;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function getKeyType(): string
    {
        return 'string';
    }

}
