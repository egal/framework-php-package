<?php

declare(strict_types=1);

namespace Egal\Auth\Policies;

/**
 * @method static bool retrieving(string $name, array $arguments))
 * @method static bool retrieved(string $name, array $arguments))
 * @method static bool retrievingMetadata(string $name, array $arguments))
 * @method static bool retrievedMetadata(string $name, array $arguments))
 * @method static bool retrievingCount(string $name, array $arguments))
 * @method static bool retrievedCount(string $name, array $arguments))
 * @method static bool creating(string $name, array $arguments))
 * @method static bool created(string $name, array $arguments))
 * @method static bool updating(string $name, array $arguments))
 * @method static bool updated(string $name, array $arguments))
 * @method static bool deleting(string $name, array $arguments))
 * @method static bool deleted(string $name, array $arguments))
 */
class DenyAllPolicy
{

    public static function __callStatic(string $name, array $arguments): bool
    {
        return false;
    }

}
