<?php

declare(strict_types=1);

namespace Egal\Model;

use Egal\Core\Session\Session;
use Egal\Model\Exceptions\ObjectNotFoundException;
use Egal\Model\Exceptions\UpdateManyException;
use Egal\Model\Facades\ModelMetadataManager;
use Egal\Model\Filter\FilterPart;
use Egal\Model\Traits\FilterConditionApplier;
use Egal\Model\Traits\HasDefaultLimits;
use Egal\Model\Traits\HasEvents;
use Egal\Model\Traits\HashGuardable;
use Egal\Model\Traits\Pagination;
use Egal\Model\Traits\UsesBuilder;
use Egal\Model\Traits\UsesModelMetadata;
use Egal\Model\Traits\UsesValidator;
use Egal\Model\Traits\XssGuardable;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\DB;
use Egal\Model\Traits\HasRelationships;

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
    use FilterConditionApplier;
    use HasRelationships;

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
    final public static function actionGetMetadata(): array
    {
        $instance = new static();
        Session::client()->mayOrFail('retrievingMetadata', $instance);
        $result = ModelMetadataManager::getModelMetadata(static::class)->toArray(true);
        Session::client()->mayOrFail('retrievedMetadata', $instance);

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
    final public static function actionGetItem($key, array $relations = []): array
    {
        $instance = new static();
        Session::client()->mayOrFail('retrieving', $instance);
        $instance->validateKey($key);

        /** @var Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null $item */
        $item = $instance->newQuery()
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
    final public static function actionGetItems(
        ?array $pagination = null,
        array $relations = [],
        array $filter = [],
        array $order = []
    ): array {

        $instance = new static();
        Session::client()->mayOrFail('retrieving', $instance);
        $builder = $instance->newQuery()
            ->setOrderFromArray($order)
            ->setFilterFromArray($filter)
            ->setWithFromArray($relations);

        if (isset($pagination)) {
            $paginator = $builder->difficultPaginateFromArray($pagination);
            $result = [
                'current_page' => $paginator->currentPage(),
                'total_count' => $paginator->total(),
                'per_page' => $paginator->perPage(),
            ];
            $items = collect($paginator->items());
        } else {
            $result = [];
            $items = $builder->limit()->get();
        }

        foreach ($items as $item) Session::client()->mayOrFail('retrieved', $item);

        $result['items'] = $items->toArray();

        return $result;
    }

    /**
     * Get count entities.
     *
     * @param mixed[] $filter
     */
    final public static function actionGetCount(array $filter = []): array
    {
        $instance = new static();
        Session::client()->mayOrFail('retrievingCount', $instance);
        $count = $instance->newQuery()
            ->setFilterFromArray($filter)
            ->count();

        Session::client()->mayOrFail('retrievedCount', $instance);

        return ['count' => $count];
    }

    /**
     * Entity creation.
     *
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] The created entity as an associative array.
     */
    final public static function actionCreate(array $attributes = [], array $relations = []): array
    {
        $entity = new static();
        $entity->fill($attributes);
        Session::client()->mayOrFail('creating', $entity);

        DB::beginTransaction();

        try {
            $entity->save();
            foreach ($relations as $name => $value) $entity->saveRelation($name, $value);
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
    final public static function actionCreateMany(array $objects = []): array
    {
        $model = new static();
        $model->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        $collection = new Collection();


        foreach ($objects as $attributes) {
            $entity = new static();
            $entity->fill($attributes);
            Session::client()->mayOrFail('creating', $entity);
            $collection->add($entity);
        }

        DB::beginTransaction();

        /** @var Model $entity */
        foreach ($collection as $entity) {
            try {
                $entity->save();
                Session::client()->mayOrFail('created', $entity);
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }
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
    final public static function actionUpdate($key, array $attributes = [], array $relations = []): array
    {
        $instance = new static();
        $instance->validateKey($key);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($key);

        if (!$entity) {
            throw ObjectNotFoundException::make($key);
        }

        $entity->fill($attributes);
        Session::client()->mayOrFail('updating', $entity);

        DB::beginTransaction();

        try {
            $entity->save();
            foreach ($relations as $name => $value) $entity->saveRelation($name, $value);
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
    final public static function actionUpdateMany(array $objects = []): array
    {
        $instance = new static();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        DB::beginTransaction();

        $collection = new Collection();
        foreach ($objects as $objectIndex => $attributes) {
            if (!isset($attributes[$instance->getKeyName()])) {
                DB::rollBack();

                throw new UpdateManyException('Object not specified index ' . $objectIndex . '!');
            }

            $key = $attributes[$instance->getKeyName()];
            $instance->validateKey($key);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($key);

            if (!$entity) {
                DB::rollBack();

                throw ObjectNotFoundException::make($key);
            }

            $entity->fill($attributes);
            Session::client()->mayOrFail('updating', $entity);

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
    final public static function actionUpdateBatch(array $filter = [], array $attributes = []): array
    {
        $instance = new static();
        $builder = $instance->newQuery();
        $filter === [] ?: $builder->setFilter(FilterPart::fromArray($filter));
        /** @var \Egal\Model\Model[]|\Illuminate\Database\Eloquent\Collection $entities */
        $entities = $builder->get();
        $builder->getModel()->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail($entities->count());

        DB::beginTransaction();

        try {
            foreach ($entities as $key => $entity) {
                $entity->fill($attributes);
                Session::client()->mayOrFail('updating', $entity);

                $entity->save();
                Session::client()->mayOrFail('updated', $entity);

                $entities[$key] = $entity;
            }
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
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
    final public static function actionDelete($key): array
    {
        $instance = new static();
        $instance->validateKey($key);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($key);
        if (!$entity) throw ObjectNotFoundException::make($key);
        Session::client()->mayOrFail('deleting', $entity);

        DB::beginTransaction();

        try {
            $entity->delete();
            Session::client()->mayOrFail('deleted', $entity);
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }

        DB::commit();

        return ['message' => 'Entity deleted!'];
    }

    /**
     * Multiple deletion of entities
     *
     * @param int[]|string[] $keys Array of identifiers for the entities to be deleted.
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    final public static function actionDeleteMany(array $keys): ?bool
    {
        $instance = new static();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($keys));
        DB::beginTransaction();

        foreach ($keys as $key) {
            $instance->validateKey($key);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($key);

            if (!$entity) {
                DB::rollBack();

                throw ObjectNotFoundException::make($key);
            }

            Session::client()->mayOrFail('deleting', $entity);

            try {
                $entity->delete();
                Session::client()->mayOrFail('deleted', $entity);
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
    final public static function actionDeleteBatch(array $filter = []): array
    {
        $instance = new static();
        $builder = $instance->newQuery();
        $filter === [] ?: $builder->setFilter(FilterPart::fromArray($filter));
        $entities = $builder->get();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail($entities->count());

        DB::beginTransaction();

        foreach ($entities as $entity) {
            try {
                Session::client()->mayOrFail('deleting', $entity);
                $entity->delete();
                Session::client()->mayOrFail('deleted', $entity);
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }
        }

        DB::commit();

        return ['message' => 'Entities has been removed!'];
    }

}
