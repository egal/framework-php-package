<?php

namespace Egal\Model;

use Egal\Model\Exceptions\FilterException;
use Egal\Model\Exceptions\OrderException;
use Egal\Model\Filter\FilterCombiner;
use Egal\Model\Filter\FilterCondition;
use Egal\Model\Filter\FilterPart;
use Egal\Model\Order\Order;
use Egal\Model\Order\OrderDirectionType;
use Egal\Model\Pagination\Pagination;
use Illuminate\Pagination\LengthAwarePaginator;

class Collection extends \Illuminate\Support\Collection
{
    private const EQUAL_OPERATOR = 'eq';
    private const NOT_EQUAL_OPERATOR = 'ne';
    private const GREATER_THEN_OPERATOR = 'gt';
    private const GREATER_OR_EQUAL_OPERATOR = 'ge';
    private const LESS_THEN_OPERATOR = 'lt';
    private const LESS_OR_EQUAL_OPERATOR = 'le';

    /**
     * @var Model|EnumModel
     */
    protected $model;

    private static function getFilterConditionArrayFromFilterPart(FilterPart $filterPart)
    {
        $filterPartContent = $filterPart->getContent();

        foreach ($filterPartContent as $key => $filterItem) {
            if ($filterItem instanceof FilterCombiner) {
                continue;
            }

            $operator = $key === 0 || strtoupper($filterPartContent[$key - 1]->getValue()) === FilterCombiner::AND
                ? FilterCombiner::AND
                : FilterCombiner::OR;

            if ($filterItem instanceof FilterCondition) {
                $filterField = $filterItem->getField();
                $filterOperator = $filterItem->getOperator();
                $filterValue = $filterItem->getValue();

                $filterConditionArray[] = [
                    'operator' => $operator,
                    'filterField' => $filterField,
                    'filterOperator' => $filterOperator,
                    'filterValue' => $filterValue
                ];
                continue;
            }

            if ($filterItem instanceof FilterPart) {
                $filterConditionArray[] = [
                    'operator' => $operator,
                    'nestedFilter' => static::getFilterConditionArrayFromFilterPart($filterItem)
                ];
                continue;
            }

            throw new FilterException();
        }

        return $filterConditionArray;
    }

    public function setFilterFromArray(array $array): self
    {
        if ($array !== []) {
              return $this->setFilter(FilterPart::fromArray($array));
        }

        return $this;
    }

    public function setOrderFromArray(array $array): self
    {
        if ($array !== []) {
            $this->setOrder(Order::fromArray($array));
        }

        return $this;
    }

    public function setOrder($order): self
    {
        if ($order instanceof Order) {
            if ($order->getDirection() === OrderDirectionType::DESC) {
                $this->sortByDesc($order->getColumn());
            } elseif ($order->getDirection() === OrderDirectionType::ASC) {
                $this->sortBy($order->getColumn());
            }
        } elseif (is_array_of_classes($order, Order::class)) {
            /** @var \Egal\Model\Order\Order $orderItem */
            foreach ($order as $orderItem) {
                if ($orderItem->getDirection() === OrderDirectionType::DESC) {
                    $this->sortByDesc($orderItem->getColumn());
                } elseif ($orderItem->getDirection() === OrderDirectionType::ASC) {
                    $this->sortBy($orderItem->getColumn());
                }
            }
        } else {
            throw new OrderException();
        }

        return $this;
    }

    private function setFilter(FilterPart $filterPart): self
    {
        $filterConditionArray = static::getFilterConditionArrayFromFilterPart($filterPart);
        return $this->filter(function ($value, $key) use ($filterConditionArray) {
            return static::getFilterValue($filterConditionArray, $key, $value);
        });
    }

    private static function constructComparison(string $operator, $firstValue, $secondValue): string
    {
        switch ($operator) {
            case self::EQUAL_OPERATOR:
                return $firstValue === $secondValue;
            case self::NOT_EQUAL_OPERATOR:
                return $firstValue != $secondValue;
            case self::GREATER_THEN_OPERATOR:
                return $firstValue > $secondValue;
            case self::LESS_THEN_OPERATOR:
                return $firstValue < $secondValue;
            case self::GREATER_OR_EQUAL_OPERATOR:
                return $firstValue >= $secondValue;
            case self::LESS_OR_EQUAL_OPERATOR:
                return $firstValue <= $secondValue;
            default:
                throw new FilterException('Incorrect operator!');
        }
    }

    private static function getFilterValue(array $filterConditionArray, $key, $value)
    {
        $filterValue = null;
        foreach ($filterConditionArray as $filterCondition) {
            if (array_key_exists('nestedFilter', $filterCondition)) {
                $comparison = static::getFilterValue(
                    $filterCondition['nestedFilter'],
                    $key,
                    $filterValue
                );
                switch ($filterCondition['operator']) {
                    case FilterCombiner::AND:
                        $filterValue = $key === 1 ? (bool)$comparison : $filterValue && ($comparison);
                        break;
                    case FilterCombiner::OR:
                        $filterValue = $filterValue || ($comparison);
                        break;
                }
            } else {
                $comparison = static::constructComparison(
                    $filterCondition['filterOperator'],
                    $value[$filterCondition['filterField']],
                    $filterCondition['filterValue']
                );

                switch ($filterCondition['operator']) {
                    case FilterCombiner::AND:
                        $filterValue = $key === 0 ? (bool)$comparison : $filterValue && $comparison;
                        break;
                    case FilterCombiner::OR:
                        $filterValue = $filterValue || $comparison;
                        break;
                }
            }
        }

        return $filterValue;
    }

    public function paginate(?array $paginationArray)
    {
        $pagination = Pagination::fromArray($paginationArray === null ? [] : $paginationArray);
        $pagination->getPage() ?: $pagination->setPage($this->model->getPage());
        $pagination->getPerPage() ?: $pagination->setPerPage($this->model->getMaxPerPage());
        $page = $pagination->getPage();
        $perPage = $pagination->getPerPage();

        return new LengthAwarePaginator($this->forPage($page, $perPage)->values(), $this->count(), $perPage, $page);
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function filter(callable $callback = null)
    {
        $collection = parent::filter($callback);
        $collection->setModel($this->model);

        return $collection;
    }
}
