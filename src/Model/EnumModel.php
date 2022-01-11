<?php

namespace Egal\Model;

use Egal\Model\Exceptions\ObjectNotFoundException;
use Egal\Model\Traits\Pagination;
use ReflectionException;

abstract class EnumModel
{
    use Pagination;

    protected static array $cache = [];

    protected int $perPage = 15;

    public static function descriptions(): array
    {
        return [];
    }

    /**
     * @throws ReflectionException
     */
    public function getItemsCollection(): Collection
    {
        $class = static::class;

        if (!isset(static::$cache[$class])) {
            $items = $this->newCollection();

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

    public static function actionGetItems(?array $pagination, array $filter = [], array $order = []): array
    {
        $instance = new static();
        $items = $instance->getItemsCollection()
            ->setFilterFromArray($filter)
            ->setOrderFromArray($order)
            ->values();

        $paginator = $items->paginate($pagination);

        return [
            'items' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'total_count' => $paginator->total(),
            'per_page' => $paginator->perPage(),
        ];
    }

    public static function actionGetItem($keyValue): array
    {
        $instance = new static();
        $item = $instance->getItemsCollection()
            ->where('key', $keyValue)
            ->first();

        if (!$item) {
            throw ObjectNotFoundException::make($keyValue);
        }

        return $item;
    }

    public static function actionGetCount(): array
    {
        $instance = new static();
        $collection = $instance->getItemsCollection();

        return [
            'count' => $collection->count()
        ];
    }

    private function newCollection()
    {
        $collection = new Collection();
        $collection->setModel($this);
        return $collection;
    }

    /**
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

}
