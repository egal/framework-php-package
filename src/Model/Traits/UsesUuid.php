<?php

declare(strict_types=1);

namespace Egal\Model\Traits;

/**
 * @mixin \Egal\Model\Model
 * @depricated since v2.0.0, use {@see UsesUuidKey}
 */
trait UsesUuid
{

    use UsesUuidKey;

    protected static function bootUsesUuid(): void
    {
        self::bootUsesUuidKey();
    }

}
