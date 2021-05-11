<?php

namespace EgalFramework\Common;

use Closure;
use EgalFramework\Common\Interfaces\APIContainer\ParserInterface;
use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;
use EgalFramework\Common\Interfaces\AppMenuInterface;
use EgalFramework\Common\Interfaces\CommandManagerInterface;
use EgalFramework\Common\Interfaces\FilterQueryInterface;
use EgalFramework\Common\Interfaces\Kerberos\KerberosInterface;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\ModelManagerInterface;
use EgalFramework\Common\Interfaces\QueueInterface;
use EgalFramework\Common\Interfaces\Request\SendInterface;
use EgalFramework\Common\Interfaces\RoleManagerInterface;
use Illuminate\Cache\RedisStore;

/**
 * Class Session
 *
 * Contains session parameters like Message, Cache, Metadata, etc...
 *
 * @package EgalFramework\CommandLine
 */
class Session
{

    private static MessageInterface $message;

    /** @var  MetadataInterface[] */
    private static array $metadata;

    private static ModelManagerInterface $modelManager;

    private static CommandManagerInterface $commandManager;

    private static RoleManagerInterface $roleManager;

    private static FilterQueryInterface $filterQuery;

    private static Closure $validateCallback;

    private static Registry $registry;

    private static AppMenuInterface $menu;

    private static QueueInterface $queue;

    private static StorageInterface $apiStorage;

    private static ParserInterface $apiParser;

    private static KerberosInterface $kerberosApi;

    private static SendInterface $sendRequest;

    private static RedisStore $requestCache;

    private static Closure $queueFaultCallback;

    public static function setMessage(MessageInterface $message): void
    {
        self::$message = $message;
    }

    public static function setModelManager(ModelManagerInterface $modelManager): void
    {
        self::$modelManager = $modelManager;
    }

    public static function setCommandManager(CommandManagerInterface $commandManager): void
    {
        self::$commandManager = $commandManager;
    }

    public static function setRoleManager(RoleManagerInterface $roleManager): void
    {
        self::$roleManager = $roleManager;
    }

    public static function getRoleManager(): RoleManagerInterface
    {
        return self::$roleManager;
    }

    public static function setFilterQuery(FilterQueryInterface $filterQuery): void
    {
        self::$filterQuery = $filterQuery;
    }

    public static function setValidateCallback(Closure $callback)
    {
        self::$validateCallback = $callback;
    }

    /**
     * @return MessageInterface
     */
    public static function getMessage(): ?MessageInterface
    {
        return isset(self::$message)
            ? self::$message
            : null;
    }

    public static function getModelManager(): ModelManagerInterface
    {
        return self::$modelManager;
    }

    public static function getCommandManager(): CommandManagerInterface
    {
        return self::$commandManager;
    }

    public static function getMetadata(string $name): MetadataInterface
    {
        if (!isset(self::$metadata[$name])) {
            $metadataPath = self::$modelManager->getMetadataPath($name);
            self::$metadata[$name] = new $metadataPath;
        }
        return self::$metadata[$name];
    }

    public static function getFilterQuery(): FilterQueryInterface
    {
        return clone(self::$filterQuery);
    }

    /**
     * @return Closure
     */
    public static function getValidateCallback(): Closure
    {
        return self::$validateCallback;
    }

    public static function setRegistry(Registry $registry): void
    {
        self::$registry = $registry;
    }

    public static function getRegistry(): Registry
    {
        return self::$registry;
    }

    public static function setMenu(AppMenuInterface $menu): void
    {
        self::$menu = $menu;
    }

    public static function getMenu(): AppMenuInterface
    {
        return self::$menu;
    }

    public static function setQueue(QueueInterface $queue): void
    {
        self::$queue = $queue;
    }

    public static function getQueue(): QueueInterface
    {
        return clone self::$queue;
    }

    public static function setApiStorage(StorageInterface $apiStorage): void
    {
        self::$apiStorage = $apiStorage;
    }

    public static function getApiStorage(): StorageInterface
    {
        return self::$apiStorage;
    }

    public static function setApiParser(ParserInterface $apiParser): void
    {
        self::$apiParser = $apiParser;
    }

    public static function getApiParser(): ParserInterface
    {
        return self::$apiParser;
    }

    public static function setKerberosApi(KerberosInterface $kerberosApi): void
    {
        self::$kerberosApi = $kerberosApi;
    }

    public static function getKerberosApi(): KerberosInterface
    {
        return self::$kerberosApi;
    }

    /**
     * @param SendInterface $sendRequest
     */
    public static function setSendRequest(SendInterface $sendRequest): void
    {
        self::$sendRequest = $sendRequest;
    }

    public static function getSendRequest(): ?SendInterface
    {
        return isset(self::$sendRequest)
            ? self::$sendRequest
            : null;
    }

    public static function setRequestCache(RedisStore $requestCache): void
    {
        self::$requestCache = $requestCache;
    }

    public static function getRequestCache(): RedisStore
    {
        return self::$requestCache;
    }

    public static function setQueueFaultCallback(Closure $queueFaultCallback): void
    {
        self::$queueFaultCallback = $queueFaultCallback;
    }

    public static function getQueueFaultCallback(): ?Closure
    {
        return isset(self::$queueFaultCallback)
            ? self::$queueFaultCallback
            : null;
    }

}
