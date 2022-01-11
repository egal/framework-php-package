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
    public static function getItemsCollection(): Collection
    {
        $class = static::class;

        if (!isset(static::$cache[$class])) {
            $items = new Collection();

            $reflection = new \ReflectionClass($class);
            $keyValuesArray = $reflection->getConstants();

            foreach ($keyValuesArray as $key => $value) {
                $item['key'] = $key;
                $item['value'] = $value;
                $item['description'] = static::descriptions()[$value];
                $items->push($item);
            }

            static::$cache[$class] = $items;
        }

        return static::$cache[$class];
    }

    public static function actionGetItems(array $filter = [], array $order = []): array
    {
        $items = static::getItemsCollection()
            ->setFilterFromArray($filter)
            ->setOrderFromArray($order)
            ->values();

        return [
            'items' => $items->toArray(),
        ];
    }

    public static function actionGetItem($keyValue): array
    {
        $item = static::getItemsCollection()
            ->where('key', $keyValue)
            ->firstOrFail();
        return $item;
    }

    public static function actionGetCount(): array
    {
        return ['count' => static::getItemsCollection()->count()];
    }

}
