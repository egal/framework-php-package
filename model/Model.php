<?php

declare(strict_types=1);

namespace Egal\Model;

use Egal\Model\Exceptions\DeleteManyException;
use Egal\Model\Exceptions\NotFoundException;
use Egal\Model\Exceptions\UpdateException;
use Egal\Model\Exceptions\UpdateManyException;
use Egal\Model\Filter\FilterPart;
use Egal\Model\Traits\FilterConditionApplier;
use Egal\Model\Traits\HasDefaultLimits;
use Egal\Model\Traits\HasEvents;
use Egal\Model\Traits\HashGuardable;
use Egal\Model\Traits\InstanceForAction;
use Egal\Model\Traits\Pagination;
use Egal\Model\Traits\UsesBuilder;
use Egal\Model\Traits\UsesModelMetadata;
use Egal\Model\Traits\UsesValidator;
use Egal\Model\Traits\XssGuardable;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\DB;

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
    use UsesBuilder;
    use UsesModelMetadata;
    use UsesValidator;
    use XssGuardable;
    use InstanceForAction;
    use FilterConditionApplier;

    /**
     * The default number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 10;

    /**
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are mass assignable.
     *
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
     * @throws \Egal\Core\Exceptions\ModelNotFoundException
     */
    public static function actionGetMetadata(): array
    {
        return ModelManager::getModelMetadata(static::class)->toArray();
    }

    /**
     * Getting entity.
     *
     * @param int|string $id Entity identification.
     * @param string[] $withs Array of relations displayed for an entity.
     * @return mixed[] Entity as an associative array.
     * @throws \Egal\Model\Exceptions\ValidateException
     */
    public static function actionGetItem($id, array $withs = []): array
    {
        $instance = static::newInstanceForAction();
        $instance->validateKey($id);

        return $instance->newQuery()
            ->needFireModelActionEvents()
            ->where('id', '=', $id)
            ->with($withs)
            ->firstOrFail()
            ->toArray();
    }

    /**
     * Getting a array of entities
     *
     * @param mixed[]|null $pagination Entity pagination array.
     * Further transformed into {@see \Egal\Model\Order\Order}[].
     * If not specified, the full list of entities will be displayed.
     * Example: [
     *   "page": 1,
     *   "per_page": 10
     * ].
     * @param string[] $withs Array of relations displayed for an entity.
     * @param mixed[] $filter Serialized array from {@see \Egal\Model\Filter\FilterPart}.
     * Example: [
     *   ["name", "eq", "John"],
     *   "OR",
     *   [
     *     ["age", "ge", 20],
     *     "AND",
     *     ["age", "le", 20]
     *   ]
     * ].
     * @param mixed[] $order Sorting array of displayed entities, then converted to {@see \Egal\Model\Order\Order}[].
     * Example: [
     *   ["column" => "name", "direction" => "asc"],
     *   ["column" => "age", "direction" => "desc"]
     * ].
     * @return mixed[] The result of the query and the paginator as an associative array.
     * Example: [
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
     * ].
     * @throws \Egal\Model\Exceptions\FilterException|\Egal\Model\Exceptions\OrderException|\ReflectionException
     */
    public static function actionGetItems(
        ?array $pagination = null,
        array $withs = [],
        array $filter = [],
        array $order = []
    ): array {
        $builder = static::newInstanceForAction()
            ->newQuery()
            ->needFireModelActionEvents()
            ->setOrderFromArray($order)
            ->setFilterFromArray($filter)
            ->setWithFromArray($withs);

        if (isset($pagination)) {
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
     * Entity creation.
     *
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] The created entity as an associative array.
     */
    public static function actionCreate(array $attributes = []): array
    {
        $entity = static::newInstanceForAction();
        $entity->fill($attributes);
        $entity->needFireActionEvents();
        $entity->save();

        return $entity->toArray();
    }

    /**
     * Multiple entity creation.
     *
     * @param mixed[] $objects Array of objects to create.
     * @return mixed[] Array of created objects.
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException
     */
    public static function actionCreateMany(array $objects = []): array
    {
        $instance = static::newInstanceForAction();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        $collection = new Collection();
        DB::beginTransaction();

        foreach ($objects as $attributes) {
            $entity = $instance->newInstance();
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
     * @param int|string|null $id Entity identification.
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] Updated entity as an associative array.
     * @throws \Egal\Model\Exceptions\ValidateException|\Egal\Model\Exceptions\UpdateException
     */
    public static function actionUpdate($id = null, array $attributes = []): array
    {
        if (!isset($id)) {
            $modelInstance = new static();

            if (!isset($attributes[$modelInstance->getKeyName()])) {
                throw new UpdateException('The identifier of the entity being updated is not specified!');
            }

            $id = $attributes[$modelInstance->getKeyName()];
        }

        $instance = static::newInstanceForAction();
        $instance->validateKey($id);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->findOrFail($id);
        $entity->needFireActionEvents();
        $entity->update($attributes);

        return $entity->toArray();
    }

    /**
     * Multiple entity updates
     *
     * @param mixed[] $objects Array of updatable objects (objects must contain an identification key).
     * @return mixed[]
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException|\Egal\Model\Exceptions\UpdateManyException
     */
    public static function actionUpdateMany(array $objects = []): array
    {
        $collection = new Collection();
        $instance = static::newInstanceForAction();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        DB::beginTransaction();

        foreach ($objects as $objectIndex => $attributes) {
            if (!isset($attributes[$instance->getKeyName()])) {
                DB::rollBack();

                throw new UpdateManyException('Object not specified index ' . $objectIndex . '!');
            }

            $key = $attributes[$instance->getKeyName()];
            $instance = static::newInstanceForAction();
            $instance->validateKey($key);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($key);

            if (!$entity) {
                DB::rollBack();

                throw new UpdateManyException('Object not found with ' . $objectIndex . ' index!');
            }

            $entity->needFireActionEvents();
            $entity->fill($attributes);
            $entity->save();
            $collection->add($entity);
        }

        DB::commit();

        return $collection->toArray();
    }

    /**
     * Multiple update of entities by filter.
     *
     * @param mixed[] $filter Serialized array from {@see \Egal\Model\Filter\FilterPart}.
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] Updated entities.
     * @throws \Egal\Model\Exceptions\FilterException|\Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException|\Exception
     */
    public static function actionUpdateManyRaw(array $filter = [], array $attributes = []): array
    {
        $builder = static::newInstanceForAction()->newQuery();
        $filter === [] ?: $builder->setFilter(FilterPart::fromArray($filter));
        /** @var \Egal\Model\Model[]|\Illuminate\Database\Eloquent\Collection $entities */
        $entities = $builder->get();
        $builder->getModel()->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail($entities->count());
        DB::beginTransaction();

        foreach ($entities as $key => $entity) {
            $entity->needFireActionEvents();
            $entity->fill($attributes);

            try {
                $entity->save();
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }

            $entity->refresh();
            $entities[$key] = $entity;
        }

        DB::commit();

        return $entities->toArray();
    }

    /**
     * Deleting an entity.
     *
     * @param int|string $id Entity identification.
     * @return string[]
     * @throws \Egal\Model\Exceptions\NotFoundException
     */
    public static function actionDelete($id): array
    {
        $instance = static::newInstanceForAction();
        $instance->validateKey($id);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($id);

        if (!$entity) {
            throw new NotFoundException();
        }

        $entity->needFireActionEvents();
        $entity->delete();

        return ['message' => 'Entity deleted!'];
    }

    /**
     * Multiple deletion of entities
     *
     * @param int[]|string[] $ids Array of identifiers for the entities to be deleted.
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException|\Egal\Model\Exceptions\DeleteManyException
     * @throws \Exception
     */
    public static function actionDeleteMany(array $ids): ?bool
    {
        $instance = static::newInstanceForAction();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($ids));
        DB::beginTransaction();

        foreach ($ids as $id) {
            $instance = static::newInstanceForAction();
            $instance->validateKey($id);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($id);

            if (!$entity) {
                DB::rollBack();

                throw new DeleteManyException('Object not found with index  ' . $id . '!');
            }

            try {
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
     * Multiple deletion of entities by filter.
     *
     * @param mixed[] $filter Serialized array from {@see \Egal\Model\Filter\FilterPart}.
     * @return array
     * @throws \Egal\Model\Exceptions\FilterException|\Exception
     */
    public static function actionDeleteManyRaw(array $filter = []): array
    {
        $builder = static::newInstanceForAction()->newQuery();
        $filter === [] ?: $builder->setFilter(FilterPart::fromArray($filter));
        $entities = $builder->get();
        $builder->getModel()->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail($entities->count());

        DB::beginTransaction();

        foreach ($entities as $entity) {
            try {
                $entity->needFireActionEvents();
                $entity->delete();
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }
        }

        DB::commit();

        return ['message' => 'Entities has been removed!'];
    }

}
