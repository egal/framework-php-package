<?php

namespace EgalFramework\Common;

class Settings
{

    const DEFAULT_MAX_RELATIONS = 100;

    private static bool $debugMode;

    private static bool $disableAuth;

    private static bool $disableCache;

    private static string $appName;

    private static string $appKey;

    private static int $defaultMaxRelations;

    private static bool $isAuth;

    private static string $hostAddress;

    /** @var int[] */
    private static array $maxRelationsByRole;

    public static function setDebugMode(bool $debugMode): void
    {
        self::$debugMode = $debugMode;
    }

    public static function getDebugMode(): bool
    {
        return self::$debugMode;
    }

    public static function setDisableAuth(bool $disableAuth): void
    {
        self::$disableAuth = $disableAuth;
    }

    public static function getDisableAuth(): bool
    {
        return self::$disableAuth;
    }

    public static function setDisableCache(bool $disableCache): void
    {
        self::$disableCache = $disableCache;
    }

    public static function getDisableCache(): bool
    {
        return self::$disableCache;
    }

    public static function setAppName(string $appName): void
    {
        self::$appName = $appName;
    }

    public static function getAppName(): string
    {
        return self::$appName;
    }

    public static function setAppKey(string $appKey): void
    {
        self::$appKey = $appKey;
    }

    public static function getAppKey(): string
    {
        return self::$appKey;
    }

    public static function setDefaultMaxRelations(int $defaultMaxRelations): void
    {
        self::$defaultMaxRelations = $defaultMaxRelations;
    }

    public static function getDefaultMaxRelations(): int
    {
        return isset(self::$defaultMaxRelations)
            ? self::$defaultMaxRelations
            : self::DEFAULT_MAX_RELATIONS;
    }

    public static function setMaxRelationsByRole(string $role, int $maxRelations): void
    {
        if (!isset(self::$maxRelationsByRole) || !is_array(self::$maxRelationsByRole)) {
            self::$maxRelationsByRole = [];
        }
        self::$maxRelationsByRole[$role] = $maxRelations;
    }

    public static function getMaxRelationsByRole(string $role): ?int
    {
        if (!isset(self::$maxRelationsByRole) || !is_array(self::$maxRelationsByRole)) {
            return null;
        }
        return isset(self::$maxRelationsByRole[$role])
            ? self::$maxRelationsByRole[$role]
            : null;
    }

    public static function setIsAuth(bool $isAuth): void
    {
        self::$isAuth = $isAuth;
    }

    public static function getIsAuth(): bool
    {
        return self::$isAuth;
    }

    public static function getHostAddress(): string
    {
        return self::$hostAddress;
    }

    public static function setHostAddress(string $hostAddress): void
    {
        self::$hostAddress = $hostAddress;
    }

}
