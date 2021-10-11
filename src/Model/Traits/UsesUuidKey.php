<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

use Illuminate\Support\Str;

/**
 * @mixin \Egal\Model\Model
 */
trait UsesUuidKey
{

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }

    protected static function bootUsesUuidKey(): void
    {
        static::creating(static function ($model): void {
            /** @var \Egal\Model\Model $model */
            $model->setAttribute($model->getKeyName(), (string) Str::uuid());
        });
    }

}
