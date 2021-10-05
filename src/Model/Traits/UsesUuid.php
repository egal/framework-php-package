<?php

namespace Egal\Model\Traits;

use Egal\Model\Model;

/**
 * @mixin Model
 * @depricated since v2.0.0, use {@see UsesUuidKey}
 */
trait UsesUuid
{

    use UsesUuidKey;

    protected static function bootUsesUuid()
    {
        self::bootUsesUuidKey();
    }

}
