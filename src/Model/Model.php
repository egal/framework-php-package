<?php

declare(strict_types=1);

namespace Egal\Model;

use Egal\Model\Exceptions\ObjectNotFoundException;
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
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionGetItem($id, array $withs = []): array
    {
        $instance = new static();
        $instance->makeIsInstanceForAction();
        $instance->validateKey($id);

        $item = $instance->newQuery()
            ->makeModelIsInstanceForAction()
            ->with($withs)
            ->find($id);

        if (!$item) {
            throw ObjectNotFoundException::make($id);
        }

        return $item->toArray();
    }

    /**
     * Getting a array of entities
     *
     * @param mixed[]|null $pagination Entity pagination array.
     * @param string[] $withs Array of relations displayed for an entity.
     * @param mixed[] $filter Serialized array from {@see \Egal\Model\Filter\FilterPart}.
     * @param mixed[] $order Sorting array of displayed entities, then converted to {@see \Egal\Model\Order\Order}[].
     * @return mixed[] The result of the query and the paginator as an associative array.
     */
    public static function actionGetItems(
        ?array $pagination = null,
        array $withs = [],
        array $filter = [],
        array $order = []
    ): array {
        $instance = new static();
        $instance->makeIsInstanceForAction();

        $builder = $instance->newQuery()
            ->makeModelIsInstanceForAction()
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
     * Get count entityes.
     *
     * @param mixed[] $filter
     */
    public static function actionGetCount(array $filter = []): array
    {
        $instance = new static();
        $instance->makeIsInstanceForAction();

        $count = $instance->newQuery()
            ->setFilterFromArray($filter)
            ->count();

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
        $entity = new static();
        $entity->makeIsInstanceForAction();
        $entity->fill($attributes);
        $entity->save();

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
     * @param int|string|null $id Entity identification.
     * @param mixed[] $attributes Associative array of attributes.
     * @return mixed[] Updated entity as an associative array.
     * @throws \Egal\Model\Exceptions\UpdateException
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionUpdate($id = null, array $attributes = []): array
    {
        $instance = new static();

        if (!isset($id)) {
            if (!isset($attributes[$instance->getKeyName()])) {
                throw new UpdateException('The identifier of the entity being updated is not specified!');
            }

            $id = $attributes[$instance->getKeyName()];
        }

        $instance->makeIsInstanceForAction();
        $instance->validateKey($id);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($id);

        if (!$entity) {
            throw ObjectNotFoundException::make($id);
        }

        $entity->makeIsInstanceForAction();
        $entity->update($attributes);

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
        $collection = new Collection();
        $instance = new static();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($objects));
        DB::beginTransaction();

        foreach ($objects as $objectIndex => $attributes) {
            if (!isset($attributes[$instance->getKeyName()])) {
                DB::rollBack();

                throw new UpdateManyException('Object not specified index ' . $objectIndex . '!');
            }

            $id = $attributes[$instance->getKeyName()];
            $instance->makeIsInstanceForAction();
            $instance->validateKey($id);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($id);

            if (!$entity) {
                DB::rollBack();

                throw ObjectNotFoundException::make($id);
            }

            $entity->makeIsInstanceForAction();
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
     * @throws \Exception
     */
    public static function actionUpdateManyRaw(array $filter = [], array $attributes = []): array
    {
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
     * @param int|string $id Entity identification.
     * @return string[]
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionDelete($id): array
    {
        $instance = new static();
        $instance->makeIsInstanceForAction();
        $instance->validateKey($id);

        /** @var \Egal\Model\Model $entity */
        $entity = $instance->newQuery()->find($id);

        if (!$entity) {
            throw ObjectNotFoundException::make($id);
        }

        $entity->makeIsInstanceForAction();
        $entity->delete();

        return ['message' => 'Entity deleted!'];
    }

    /**
     * Multiple deletion of entities
     *
     * @param int[]|string[] $ids Array of identifiers for the entities to be deleted.
     * @throws \Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException
     * @throws \Egal\Model\Exceptions\ObjectNotFoundException
     */
    public static function actionDeleteMany(array $ids): ?bool
    {
        $instance = new static();
        $instance->isLessThanMaxCountEntitiesCanToManipulateWithActionOrFail(count($ids));
        DB::beginTransaction();

        foreach ($ids as $id) {
            $instance->makeIsInstanceForAction();
            $instance->validateKey($id);

            /** @var \Egal\Model\Model $entity */
            $entity = $instance->newQuery()->find($id);

            if (!$entity) {
                DB::rollBack();

                throw ObjectNotFoundException::make($id);
            }

            try {
                $entity->makeIsInstanceForAction();
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
     * @return mixed[]
     * @throws \Exception
     */
    public static function actionDeleteManyRaw(array $filter = []): array
    {
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
            } catch (Exception $exception) {
                DB::rollBack();

                throw $exception;
            }
        }

        DB::commit();

        return ['message' => 'Entities has been removed!'];
    }

}
