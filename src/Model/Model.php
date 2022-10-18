<?php

declare(strict_types=1);

namespace Egal\Model;

use Egal\Core\Session\Session;
use Egal\Model\Exceptions\ObjectNotFoundException;
use Egal\Model\Exceptions\OrderException;
use Egal\Model\Exceptions\UpdateManyException;
use Egal\Model\Facades\ModelMetadataManager;
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
     * @var string[]
     */
    protected $hidden = [
        'pivot',
        'laravel_through_key',
    ];

    /**
     * Retrieving Model Metadata
     */
    public static function actionGetMetadata(): array
    {
        Session::client()->mayOrFail('retrievingMetadata', static::class);
        $result = ModelMetadataManager::getModelMetadata(static::class)->toArray(true);
        Session::client()->mayOrFail('retrievedMetadata', static::class);

        return $result;
    }

    /**
     * Getting entity.
     *
     * @param int|string $key Entity identification.
     * @param string[] $relations Array of relations displayed for an entity.
     * @return mixed[] Entity as an associative array.
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionGetItem($key, array $relations = []): array
    {
        Session::client()->mayOrFail('retrieving', static::class);

        $instance = new static();
        $instance->makeIsInstanceForAction();
        $instance->validateKey($key);

        $item = $instance->newQuery()
            ->makeModelIsInstanceForAction()
            ->with($relations)
            ->find($key);

        if (!$item) {
            throw ObjectNotFoundException::make($key);
        }

        Session::client()->mayOrFail('retrieved', $item);

        return $item->toArray();
    }

    /**
     * Getting a array of entities
     *
     * @param mixed[]|null $pagination Entity pagination array.
     * @param string[] $relations Array of relations displayed for an entity.
     * @param mixed[] $filter Serialized array from {@see \Egal\Model\Filter\FilterPart}.
     * @param mixed[] $order Sorting array of displayed entities, then converted to {@see \Egal\Model\Order\Order}[].
     * @return mixed[] The result of the query and the paginator as an associative array.
     * @throws OrderException
     */
    public static function actionGetItems(
        ?array $pagination = null,
        array  $relations = [],
        array  $filter = [],
        array  $order = []
    ): array {
        Session::client()->mayOrFail('retrieving', static::class);

        $instance = new static();
        $instance->makeIsInstanceForAction();

        $builder = $instance->newQuery()
            ->makeModelIsInstanceForAction()
            ->setOrderFromArray($order)
            ->setFilterFromArray($filter)
            ->setWithFromArray($relations);

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

        foreach ($result['items'] as $item) {
            $model = new static($item);
            Session::client()->mayOrFail('retrieved', $model);
        }

        return $result;
    }

    /**
     * Get count entities.
     *
     * @param mixed[] $filter
     */
    public static function actionGetCount(array $filter = []): array
    {
        Session::client()->mayOrFail('retrievingCount', static::class);

        $instance = new static();
        $instance->makeIsInstanceForAction();

        $count = $instance->newQuery()
            ->setFilterFromArray($filter)
            ->count();

        Session::client()->mayOrFail('retrievedCount', static::class);

        return ['count' => $count];
    }

    /**
     * Entity creation.
     *
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] The created entity as an associative array.
     */
    public static function actionCreate(array $attributes = []): array
    {
        Session::client()->mayOrFail('creating', static::class);

        DB::beginTransaction();
        $entity = new static();
        $entity->makeIsInstanceForAction();
        $entity->fill($attributes);
        $entity->save();

        try {
            $entity->save();
            Session::client()->mayOrFail('created', $entity);
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }

        DB::commit();

        return $entity->toArray();
    }

    /**
     * Multiple entity creation.
     *
     * @param mixed[] $objects Array of objects to create.
     * @return mixed[] Array of created objects.
     * @throws \Exception
     */
    public static function actionCreateMany(array $objects = []): array
    {
        Session::client()->mayOrFail('creating', static::class);

        $model = new static();
        $model->makeIsInstanceForAction();
        $model->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        $collection = new Collection();
        DB::beginTransaction();

        foreach ($objects as $attributes) {
            $entity = new static();
            $entity->makeIsInstanceForAction();
            $entity->fill($attributes);

            try {
                $entity->save();
                Session::client()->mayOrFail('created', $entity);
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }

            $collection->add($entity);
        }

        DB::commit();

        return $collection->toArray();
    }

    /**
     * Entity update
     *
     * @param int|string|null $key Entity identification.
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] Updated entity as an associative array.
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     * @throws Exception
     */
    public static function actionUpdate($key, array $attributes = []): array
    {
        Session::client()->mayOrFail('updating', static::class);

        DB::beginTransaction();
        $instance = new static();
        $instance->makeIsInstanceForAction();
        $instance->validateKey($key);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($key);

        if (!$entity) {
            throw ObjectNotFoundException::make($key);
        }

        $entity->makeIsInstanceForAction();
        $entity->update($attributes);
        try {
            $entity->save();
            Session::client()->mayOrFail('updated', $entity);
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }

        DB::commit();

        return $entity->toArray();
    }

    /**
     * Multiple entity updates
     *
     * @param mixed[] $objects Array of updatable objects (objects must contain an identification key).
     * @return mixed[]
     * @throws \Egal\Model\Exceptions\UpdateManyException
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionUpdateMany(array $objects = []): array
    {
        Session::client()->mayOrFail('updating', static::class);

        $collection = new Collection();
        $instance = new static();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        DB::beginTransaction();

        foreach ($objects as $objectIndex => $attributes) {
            if (!isset($attributes[$instance->getKeyName()])) {
                DB::rollBack();

                throw new UpdateManyException('Object not specified index ' . $objectIndex . '!');
            }

            $key = $attributes[$instance->getKeyName()];
            $instance->makeIsInstanceForAction();
            $instance->validateKey($key);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($key);

            if (!$entity) {
                DB::rollBack();

                throw ObjectNotFoundException::make($key);
            }

            $entity->makeIsInstanceForAction();
            $entity->fill($attributes);

            try {
                $entity->save();
                Session::client()->mayOrFail('updated', $entity);
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }
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
     * @throws \Exception
     */
    public static function actionUpdateManyRaw(array $filter = [], array $attributes = []): array
    {
        Session::client()->mayOrFail('updating', static::class);

        $instance = new static();
        $builder = $instance->newQuery()->makeModelIsInstanceForAction();
        $filter === [] ?: $builder->setFilter(FilterPart::fromArray($filter));
        /** @var \Egal\Model\Model[]|\Illuminate\Database\Eloquent\Collection $entities */
        $entities = $builder->get();
        $builder->getModel()->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail($entities->count());
        DB::beginTransaction();

        foreach ($entities as $key => $entity) {
            $entity->makeIsInstanceForAction();
            $entity->fill($attributes);

            try {
                $entity->save();
                Session::client()->mayOrFail('updated', static::class);
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }

            $entities[$key] = $entity;
        }

        DB::commit();

        return $entities->toArray();
    }

    /**
     * Deleting an entity.
     *
     * @param int|string $key Entity identification.
     * @return string[]
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionDelete($key): array
    {
        Session::client()->mayOrFail('deleting', static::class);

        DB::beginTransaction();
        $instance = new static();
        $instance->makeIsInstanceForAction();
        $instance->validateKey($key);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($key);

        if (!$entity) {
            throw ObjectNotFoundException::make($key);
        }

        $entity->makeIsInstanceForAction();

        try {
            $entity->delete();
            Session::client()->mayOrFail('deleted', static::class);
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }

        return ['message' => 'Entity deleted!'];
    }

    /**
     * Multiple deletion of entities
     *
     * @param int[]|string[] $keys Array of identifiers for the entities to be deleted.
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionDeleteMany(array $keys): ?bool
    {
        Session::client()->mayOrFail('deleting', static::class);

        $instance = new static();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($keys));
        DB::beginTransaction();

        foreach ($keys as $key) {
            $instance->makeIsInstanceForAction();
            $instance->validateKey($key);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($key);

            if (!$entity) {
                DB::rollBack();

                throw ObjectNotFoundException::make($key);
            }

            try {
                $entity->makeIsInstanceForAction();
                $entity->delete();
                Session::client()->mayOrFail('deleted', static::class);
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
     * @return mixed[]
     * @throws \Exception
     */
    public static function actionDeleteManyRaw(array $filter = []): array
    {
        Session::client()->mayOrFail('deleting', static::class);

        $instance = new static();
        $builder = $instance->newQuery()->makeModelIsInstanceForAction();
        $filter === [] ?: $builder->setFilter(FilterPart::fromArray($filter));
        $entities = $builder->get();
        $builder->getModel()->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail($entities->count());

        DB::beginTransaction();

        foreach ($entities as $entity) {
            try {
                $entity->makeIsInstanceForAction();
                $entity->delete();
                Session::client()->mayOrFail('deleted', static::class);
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }
        }

        DB::commit();

        return ['message' => 'Entities has been removed!'];
    }

}
