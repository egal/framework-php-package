<?php

declare(strict_types=1);

namespace Egal\Core\Events;

class EventManager
{

    protected array $globalListen = [];

    protected static function getInstance(): EventManager
    {
        return app(self::class);
    }

    public static function setGlobalListen(array $globalListen): void
    {
        self::getInstance()->globalListen = $globalListen;
    }

    public static function getListeners(string $serviceName, string $modelName, string $name): array
    {
        return self::getInstance()->globalListen[$serviceName][$modelName][$name] ?? [];
    }

}
