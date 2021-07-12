<?php

namespace Egal\Model;

use Egal\Model\Exceptions\DeleteManyException;
use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\NotFoundException;
use Egal\Model\Exceptions\UpdateException;
use Egal\Model\Exceptions\UpdateManyException;
use Egal\Model\Filter\FilterPart;
use Egal\Model\Order\Order;
use Egal\Model\Traits\HasDefaultLimits;
use Egal\Model\Traits\HasEvents;
use Egal\Model\Traits\HashGuardable;
use Egal\Model\Traits\Pagination;
use Egal\Model\Traits\UsesEgalBuilder;
use Egal\Model\Traits\UsesModelMetadata;
use Egal\Model\Traits\UsesValidator;
use Egal\Model\Traits\XssGuardable;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\DB;
use ReflectionException;

/**
 * Basic Egal Model
 *
 * Contains CRUD methods:
 * {@see Model::actionGetItem()},
 * {@see Model::actionGetItems()},
 * {@see Model::actionCreate()},
 * {@see Model::actionCreateMany()},
 * {@see Model::actionUpdate()},
 * {@see Model::actionUpdateMany()},
 * {@see Model::actionUpdateManyRaw()},
 * {@see Model::actionDelete()},
 * {@see Model::actionDeleteMany()},
 * {@see Model::actionDeleteManyRaw()}
 *
 * Contains getting metadata methods:
 * {@see Model::actionGetMetadata()}
 */
abstract class Model extends EloquentModel
{

    use HasDefaultLimits;
    use HasEvents;
    use HashGuardable;
    use Pagination;
    use UsesEgalBuilder;
    use UsesModelMetadata;
    use UsesValidator;
    use XssGuardable;

    /**
     * Стандартное значение количества элементов на странице при пагинации.
     */
    protected $perPage = 10;

    /**
     * @var string[]
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'id',
        'name',
        'is_default',
    ];

    /**
     * @var string[]
     */
    protected $hidden = [
        'pivot',
        'laravel_through_key',
    ];

    /**
     * Retrieving Model Metadata
     *
     * @throws ReflectionException
     */
    public static function actionGetMetadata(): array
    {
        return ModelManager::getModelMetadata(static::class)->toArray();
    }

    /**
     * Getting entity
     *
     * @param int|string $id Entity identification
     * @param string[] $withs Array of relations displayed for an entity
     * @return array Entity as an associative array
     */
    public static function actionGetItem($id, array $withs = []): array
    {
        $item = static::query()
            ->needFireModelActionEvents()
            ->where('id', '=', $id)
            ->with($withs)
            ->firstOrFail();

        return $item->toArray();
    }

    /**
     * Getting a array of entities
     *
     * @param array|null $pagination <p>
     * Entity pagination array, further transformed into {@see Order}[].
     * If not specified, the full list of entities will be displayed.
     * Array example: [
     *   "page": 1,
     *   "per_page": 10
     * ]
     * </p>
     * @param string[] $withs Array of relations displayed for an entity.
     * @param array $filter <p>
     * Array of filters, further transformed into {@see FilterPart}.
     * Пример массива: [
     *   ["name", "eq", "John"],
     *   "OR",
     *   [
     *     ["age", "ge", 20],
     *     "AND",
     *     ["age", "le", 20]
     *   ]
     * ]
     * </p>
     * @param array $order <p>
     * Sorting array of displayed entities, then converted to {@see Order}[].
     * Array example: [
     *   ["column" => "name", "direction" => "asc"],
     *   ["column" => "age", "direction" => "desc"]
     * ]
     * </p>
     * @return array <p>
     * The result of the query and the paginator as an associative array
     * Пример: [
     *   "current_page" => 1,
     *   "total_count" => 1,
     *   "per_page" => 1,
     *   "items" => [
     *     [
     *       "id" => "4b30c48a-2d90-4dda-ba06-77e79e4f4642",
     *       "email" => "test@test.test",
     *       "roles" => [
     *           [
     *             "id" => 1,
     *             "name" => "user",
     *             "is_default" => true
     *           ]
     *         ],
     *       "permissions" => [
     *         [
     *           "id" => 1,
     *           "name" => "authenticate",
     *           "is_default" => true
     *         ]
     *       ]
     *     ]
     *   ]
     * ]
     * </p>
     * @throws Exceptions\FilterException
     * @throws Exceptions\OrderException
     * @throws ReflectionException
     */
    public static function actionGetItems(
        ?array $pagination = null,
        array $withs = [],
        array $filter = [],
        array $order = []
    ): array
    {
        $builder = self::query()
            ->needFireModelActionEvents()
            ->setOrderFromArray($order)
            ->setFilterFromArray($filter)
            ->setWithFromArray($withs);

        if (!is_null($pagination)) {
            $paginator = $builder->difficultPaginateFromArray($pagination);
            $result = [
                'current_page' => $paginator->currentPage(),
                'total_count' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'items' => $paginator->items(),
            ];
        } else {
            $result = [
                'items' => $builder->limit()->get()->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Entity creation
     *
     * @param array $attributes Associative array of attributes
     * @return array The created entity as an associative array
     */
    public static function actionCreate(array $attributes = []): array
    {
        $entity = (new static($attributes))
            ->needFireActionEvents();
        $entity->save();
        return $entity->toArray();
    }

    /**
     * Multiple entity creation
     *
     * @param array $objects Array of objects to create
     * @return array Array of created objects
     * @throws Exception
     */
    public static function actionCreateMany(array $objects = []): array
    {
        DB::beginTransaction();
        $collection = new Collection();
        foreach ($objects as $attributes) {
            $entity = new static();
            $entity->needFireActionEvents();
            $entity->fill($attributes);
            try {
                $entity->save();
            } catch (Exception $exception) {
                DB::rollBack();
                throw $exception;
            }
            $entity->refresh();

            $collection->add($entity);
        }
        DB::commit();
        return $collection->toArray();
    }

    /**
     * Entity update
     *
     * @param int|string|null $id Entity identification
     * @param array $attributes Associative array of attributes
     * @return array Updated entity as an associative array
     * @throws UpdateException
     */
    public static function actionUpdate($id = null, array $attributes = []): array
    {
        if (empty($id)) {
            $modelInstance = new static();
            if (isset($attributes[$modelInstance->getKeyName()])) {
                $id = $attributes[$modelInstance->getKeyName()];
            } else {
                throw new UpdateException(
                    'The identifier of the entity being updated is not specified!'
                );
            }
        }

        /** @var Model $entity */
        $entity = static::query()
            ->findOrFail($id);
        $entity->needFireActionEvents();
        $entity->update($attributes);

        return $entity->toArray();
    }

    /**
     * Multiple entity updates
     *
     * @param array $objects Array of updatable objects (objects must contain an identification key)
     * @return array
     * @throws UpdateManyException
     */
    public static function actionUpdateMany(array $objects = []): array
    {
        DB::beginTransaction();
        $collection = new Collection();
        $modelObject = new static();

        foreach ($objects as $key => $attributes) {
            if (!isset($attributes[$modelObject->getKeyName()])) {
                DB::rollBack();
                throw new UpdateManyException('Object not specified index ' . $key . '!');
            }
            $entity = static::query()
                ->find($attributes[$modelObject->getKeyName()]);
            if (!$entity) {
                DB::rollBack();
                throw new UpdateManyException('Object not found with ' . $key . ' index!');
            }

            /** @var Model $entity */
            $entity->needFireActionEvents();
            $entity->fill($attributes);
            $entity->save();
            $collection->add($entity);
        }

        DB::commit();
        return $collection->toArray();
    }

    /**
     * Multiple update of entities by filter
     *
     * @param array $filter
     * @param array $attributes
     * @return array
     * @throws FilterException
     * @throws Exception
     * @noinspection PhpArrayAccessCanBeReplacedWithForeachValueInspection
     */
    public static function actionUpdateManyRaw(array $filter = [], array $attributes = []): array
    {
        $builder = self::query();
        $filter == [] ?: $builder->setFilter(FilterPart::fromArray($filter));

        /** @var Model[] $entities */
        $entities = $builder->get();

        DB::beginTransaction();
        foreach ($entities as $key => $entity) {
            $entities[$key]->needFireActionEvents();
            $entities[$key]->fill($attributes);
            try {
                $entities[$key]->save();
            } catch (Exception $exception) {
                DB::rollBack();
                throw $exception;
            }
            $entities[$key]->refresh();
        }
        DB::commit();

        /** @var Collection $entities */
        return $entities->toArray();
    }

    /**
     * Deleting an entity
     *
     * @param int|string $id Entity identification
     * @return array
     * @throws NotFoundException
     * @throws Exception
     */
    public static function actionDelete($id): array
    {
        $entity = static::query()
            ->find($id);
        if (!$entity) {
            throw new NotFoundException();
        }

        /** @var Model $entity */
        $entity->needFireActionEvents();
        $entity->delete();

        return [
            'message' => "Сущность удалена!"
        ];
    }

    /**
     * Multiple deletion of entities
     *
     * @param $ids
     * @return bool|null
     * @throws DeleteManyException
     * @throws Exception
     */
    public static function actionDeleteMany($ids): ?bool
    {
        DB::beginTransaction();
        foreach ($ids as $id) {
            $entity = static::query()
                ->find($id);
            if (!$entity) {
                DB::rollBack();
                throw new DeleteManyException('Object not found with index  ' . $id . '!');
            }
            try {
                /** @var Model $entity */
                $entity->needFireActionEvents();
                $entity->delete();
            } catch (Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }
        DB::commit();
        return true;
    }

    /**
     * Multiple deletion of entities by filter
     *
     * @param array $filter
     * @return array
     * @throws FilterException
     * @throws Exception
     * @noinspection PhpArrayAccessCanBeReplacedWithForeachValueInspection
     */
    public static function actionDeleteManyRaw(array $filter = []): array
    {
        $builder = self::query();
        $filter == [] ?: $builder->setFilter(FilterPart::fromArray($filter));

        $entities = $builder->get();
        $entitiesCount = $entities->count();

        DB::beginTransaction();
        /** @var Model[] $entities */
        foreach ($entities as $key => $entity) {
            try {
                $entities[$key]->needFireActionEvents();
                $entities[$key]->delete();
            } catch (Exception $exception) {
                DB::rollBack();
                throw $exception;
            }
        }
        DB::commit();

        return [
            'message' => "Removed $entitiesCount entities!"
        ];
    }

}
