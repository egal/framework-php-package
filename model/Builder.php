<?php

namespace Egal\Model;

use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\OrderException;
use Egal\Model\Filter\FilterCombiner;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Filter\FilterPart;
use Egal\Model\Order\Order;
use Egal\Model\Pagination\Pagination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionException;
use ReflectionMethod;

/**
 * Класс формирования запросов к БД.
 *
 * По стандарту используется в {@see Model}
 *
 *
 * @package Egal\Model
 */
final class Builder extends EloquentBuilder
{

    /**
     * The model being queried.
     *
     * @var Model
     */
    protected $model;

    /**
     * Получите экземпляр отношения для данного имени отношения.
     *
     * Метод {@see EloquentBuilder::getRelation()} дополненный более точной проверкой наличия запрашиваемого отношения.
     * Список дополнительных проверок:
     * проверка на наличие метода,
     * должно быть указано возвращаемое значение на уровне PHP,
     * тип возвращаемого значения должен быть потомком {@see Relation}
     *
     * @param string $name Название отношения
     * @return Relation
     * @throws ReflectionException
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function getRelation($name)
    {
        $name = Str::camel($name);

        $model = $this->getModel();
        $modelClass = get_class($model);
        if (!method_exists($modelClass, $name)) {
            throw RelationNotFoundException::make($model, $name);
        }
        $returnType = (new ReflectionMethod($modelClass, $name))->getReturnType();
        if (is_null($returnType)) {
            throw RelationNotFoundException::make($model, $name);
        }
        /** @noinspection PhpPossiblePolymorphicInvocationInspection */
        $returnTypeName = $returnType->getName();
        if (!class_exists($returnTypeName) || !is_a($returnTypeName, Relation::class, true)) {
            throw RelationNotFoundException::make($model, $name);
        }

        return parent::getRelation($name);
    }

    /**
     * Формирование сортировки.
     *
     * @param Order|Order[] $order
     * @return Builder
     * @throws OrderException
     */
    public function setOrder($order): Builder
    {
        if ($order instanceof Order) {
            $this->orderBy($order->getColumn(), $order->getDirection());
        } elseif (is_array_of_classes($order, Order::class)) {
            /** @var Order $orderItem */
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
     * @param array $array
     * @return $this
     * @throws OrderException
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
     * @param FilterPart $filterPart
     * @return $this
     * @throws FilterException
     * @throws ReflectionException
     */
    public function setFilter(FilterPart $filterPart): Builder
    {
        $filterPartContent = $filterPart->getContent();
        foreach ($filterPartContent as $key => $filterItem) {
            $clause = $this->getWhereClause($filterItem, $key, $filterPartContent);

            if ($filterItem instanceof FilterCondition) {
                $this->applyFilterCondition($filterItem, $clause);
            } elseif ($filterItem instanceof FilterPart) {
                $this->$clause(function (Builder $query) use ($filterItem) {
                    $query->setFilter($filterItem);
                });
            }
        }

        return $this;
    }

    /**
     * Difficult filter from array.
     *
     * @param array $array
     * @return $this
     * @throws FilterException
     * @throws ReflectionException
     * @throws Exceptions\FilterException
     * @throws FilterException
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
     * @param array $array
     * @return $this
     */
    public function setWithFromArray(array $array): Builder
    {
        if ($array !== []) {
            $this->with($array);
        }

        return $this;
    }

    /**
     * Return string "where" or "orWhere"
     *
     * @param $filterItem
     * @param int $key
     * @param array $filterPartContent
     * @return string
     */
    private function getWhereClause($filterItem, int $key, array $filterPartContent): string
    {
        return $key !== 0
        && !($filterItem instanceof FilterCombiner)
        && FilterCombiner::OR === strtoupper($filterPartContent[$key - 1]->getValue())
            ? 'orWhere'
            : 'where';
    }

    /**
     * Apply filter condition to the builder query
     *
     * @param FilterCondition $filterItem
     * @param string $clause
     * @throws ReflectionException
     */
    private function applyFilterCondition(FilterCondition $filterItem, string $clause)
    {
        $relationName = $filterItem->getRelationName();
        if ($relationName) {
            $metadata = ModelManager::getModelMetadata(get_class($this->getModel()));

            if (in_array($relationName, $metadata->getRelations())) {
                $whereHasClause = $clause . 'Has';
                $this->$whereHasClause($relationName, function (Builder $query) use ($filterItem) {
                    $query->where($filterItem->getField(), $filterItem->getOperator(), $filterItem->getValue());
                });
            }
        } else {
            $this->$clause($filterItem->getField(), $filterItem->getOperator(), $filterItem->getValue());
        }
    }

    /**
     * Difficult paginate from {@see Pagination}
     *
     * @param Pagination $pagination
     * @return LengthAwarePaginator
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
     * @param array $array
     * @return LengthAwarePaginator
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
     * @return Builder
     */
    public function limit($value = null): Builder
    {
        if (is_null($value) || $value > $this->model->getMaxDisplayedCount()) {
            $value = $this->model->getMaxDisplayedCount();
        }
        return parent::limit($value);
    }

    /**
     * Get the model instance being queried.
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

}
