<?php

declare(strict_types=1);

namespace Egal\Model;

use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\OrderException;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Filter\FilterCombiner;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Filter\FilterPart;
use Egal\Model\Order\Order;
use Egal\Model\Pagination\Pagination;
use Egal\Model\With\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionMethod;

/**
 * Класс формирования запросов к БД.
 *
 * По стандарту используется в {@see Model}.
 */
class Builder extends EloquentBuilder
{

    /**
     * @var \Egal\Model\Model
     */
    protected $model;

    /**
     * Получите экземпляр отношения для данного имени отношения.
     *
     * Метод {@see EloquentBuilder::getRelation()} дополненный более точной проверкой наличия запрашиваемого отношения.
     * Список дополнительных проверок:
     * проверка на наличие метода,
     * должно быть указано возвращаемое значение на уровне PHP,
     * тип возвращаемого значения должен быть потомком {@see Relation}.
     *
     * @param string $name Название отношения.
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     * @throws \Illuminate\Database\Eloquent\RelationNotFoundException
     */
    public function getRelation($name)
    {
        $name = Str::camel($name);

        $model = $this->getModel();
        $modelClass = get_class($model);

        if (!method_exists($modelClass, $name)) {
            throw RelationNotFoundException::make($model, $name);
        }

        $refMethod = new ReflectionMethod($modelClass, $name);

        if ($refMethod->getReturnType() === null) {
            throw RelationNotFoundException::make($model, $name);
        }

        $returnTypeName = $refMethod->getReturnType()->getName();

        if (!class_exists($returnTypeName) || !is_a($returnTypeName, Relation::class, true)) {
            throw RelationNotFoundException::make($model, $name);
        }

        return parent::getRelation($name);
    }

    /**
     * Формирование сортировки.
     *
     * @param \Egal\Model\Order\Order|\Egal\Model\Order\Order[] $order
     * @throws \Egal\Model\Exceptions\OrderException
     */
    public function setOrder($order): Builder
    {
        if ($order instanceof Order) {
            $this->orderBy($order->getColumn(), $order->getDirection());
        } elseif (is_array_of_classes($order, Order::class)) {
            /** @var \Egal\Model\Order\Order $orderItem */
            foreach ($order as $orderItem) {
                $this->orderBy($orderItem->getColumn(), $orderItem->getDirection());
            }
        } else {
            throw new OrderException();
        }

        return $this;
    }

    /**
     * Difficult order from array.
     *
     * @param mixed[] $array
     * @return $this
     * @throws \Egal\Model\Exceptions\OrderException
     */
    public function setOrderFromArray(array $array): Builder
    {
        if ($array !== []) {
            $this->setOrder(Order::fromArray($array));
        }

        return $this;
    }

    /**
     * Формирование фильтра.
     *
     * @return $this
     * @throws \ReflectionException|\Egal\Model\Exceptions\FilterException
     */
    public function setFilter(FilterPart $filterPart): Builder
    {
        $this->where(static function ($query) use ($filterPart) {
            $filterPartContent = $filterPart->getContent();

            foreach ($filterPartContent as $key => $filterItem) {
                if ($filterItem instanceof FilterCombiner) {
                    continue;
                }

                $operator = $key === 0 || strtoupper($filterPartContent[$key - 1]->getValue()) === FilterCombiner::AND
                    ? FilterCombiner::AND
                    : FilterCombiner::OR;

                if ($filterItem instanceof FilterCondition) {
                    $query->applyFilterCondition($filterItem, $operator);
                    continue;
                }

                if ($filterItem instanceof FilterPart) {
                    $query->{$operator === FilterCombiner::AND ? 'where' : 'orWhere'}(
                        static function (Builder $query) use ($filterItem): void {
                            $query->setFilter($filterItem);
                        }
                    );
                    continue;
                }

                throw new FilterException();
            }
        });

        return $this;
    }

    /**
     * Difficult filter from array.
     *
     * @param mixed[] $array
     * @return $this
     * @throws \Egal\Model\Exceptions\FilterException
     * @throws \ReflectionException
     * @throws \Egal\Model\Exceptions\FilterException
     * @throws \Egal\Model\Exceptions\FilterException
     */
    public function setFilterFromArray(array $array): Builder
    {
        if ($array !== []) {
            $this->setFilter(FilterPart::fromArray($array));
        }

        return $this;
    }

    /**
     * Difficult load relations from array.
     *
     * @param string[] $array
     * @return $this
     */
    public function setWithFromArray(array $array): Builder
    {
        if ($array === []) {
            return $this;
        }

        foreach (Collection::fromArray($array)->getRelations() as $relation) {
            if (!$relation->isFilterExists()) {
                $this->with($relation->getName());
            } else {
                $this->with([
                    $relation->getName() => static function (Relation $queryRelation) use ($relation) {
                        $queryRelation->getQuery()->setFilter($relation->getFilter());
                    },
                ]);
            }
        }

        return $this;
    }

    /**
     * Difficult paginate from {@see Pagination}
     */
    public function difficultPaginate(Pagination $pagination): LengthAwarePaginator
    {
        $pagination->getPage() ?: $pagination->setPage($this->model->getPage());
        $pagination->getPerPage() ?: $pagination->setPerPage($this->model->getPerPage());

        if ($pagination->getPerPage() > $this->model->getMaxPerPage()) {
            $pagination->setPerPage($this->model->getMaxPerPage());
        }

        return $this->paginate($pagination->getPerPage(), ['*'], 'page', $pagination->getPage());
    }

    /**
     * Difficult paginate from array.
     *
     * @param mixed[] $array
     */
    public function difficultPaginateFromArray(array $array): LengthAwarePaginator
    {
        return $this->difficultPaginate(Pagination::fromArray($array));
    }

    /**
     * Set the "limit" value of the query.
     *
     * By default is {@see Model::$maxDisplayedCount}.
     *
     * If received limit great {@see Model::$maxDisplayedCount},
     * then limit will take {@see Model::$maxDisplayedCount} value.
     *
     * @param int|null $value
     */
    public function limit($value = null): Builder
    {
        if ($value === null || $value > $this->model->getMaxDisplayedCount()) {
            $value = $this->model->getMaxDisplayedCount();
        }

        return parent::limit($value);
    }

    /**
     * Get the model instance being queried.
     *
     * @return \Egal\Model\Model TODO: Strongly typed return value (non-class description) swears when used @see Mockery
     */
    public function getModel()
    {
        return $this->model;
    }

    public function makeModelIsInstanceForAction(): Builder
    {
        $this->model->makeIsInstanceForAction();

        return $this;
    }

    /**
     * Apply filter condition to the builder query
     *
     * @throws \ReflectionException|\Egal\Model\Exceptions\RelationNotFoundException|\Egal\Model\Exceptions\UnsupportedFilterConditionException
     */
    private function applyFilterCondition(FilterCondition $condition, string $beforeOperator): void
    {
        $applier = 'apply' . studly_case($condition->getOperator()) . 'FilterCondition';
        $model = $this->getModel();

        if (!method_exists($model, $applier)) {
            throw new UnsupportedFilterConditionException();
        }

        $model->$applier($this, $condition, $beforeOperator);
    }

}
