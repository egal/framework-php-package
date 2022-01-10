<?php

namespace Egal\Model;

use ReflectionException;

abstract class EnumModel
{

    protected static array $cache = [];

    public static function descriptions(): array
    {
        return [];
    }

    /**
     * @throws ReflectionException
     */
    public static function toArray()
    {
        $class = static::class;

        if (!isset(static::$cache[$class])) {
            $items = [];

            $reflection = new \ReflectionClass($class);
            $keyValuesArray = $reflection->getConstants();

            foreach ($keyValuesArray as $key => $value) {
                $item['key'] = $key;
                $item['value'] = $value;
                $item['description'] = static::descriptions()[$value];
                $items[] = $item;
            }

            static::$cache[$class] = $items;
        }

        return static::$cache[$class];
    }

    public static function actionGetItems(): array
    {
       return static::toArray();
    }

    public static function actionGetItem($keyValue): array
    {
        return array_filter(static::toArray(), function($value, $key) use ($keyValue) {
            return $key == 'key' || $value == $keyValue;
        }, ARRAY_FILTER_USE_BOTH);
    }

    public static function actionGetCount(): array
    {
        return ['count' => count(static::toArray())];
    }

}
