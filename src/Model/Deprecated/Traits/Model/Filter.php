<?php

namespace EgalFramework\Model\Deprecated\Traits\Model;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\ExceptionInterface;
use EgalFramework\Common\Interfaces\FieldInterface;
use EgalFramework\Common\Interfaces\FilterQueryInterface;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Common\RelationType;
use EgalFramework\Common\Session;
use EgalFramework\Common\SortOrder;
use EgalFramework\Model\Deprecated\Depricated\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

/**
 * @deprecated
 */
trait Filter
{

    protected MetadataInterface $metadata;

    /**
     * @return Model|Builder
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    protected function prepareFilteredQuery()
    {
        $filter = Session::getFilterQuery();
        if (!is_null($this->metadata->getDefaultMaxCount())) {
            $filter->setMaxCount($this->metadata->getDefaultMaxCount());
        }
        if (!is_null($this->metadata->getDefaultCount())) {
            $filter->setDefaultCount($this->metadata->getDefaultCount());
        }
        $filter->setQuery(Session::getMessage()->getQuery());
        $item = $this->newModelQuery();
        $this->getFullSearchQuery($filter, $item);
        $this->getRelationQuery($filter, $item);
        foreach ($filter->getFields() as $key => $value) {
            $this->setWhereValue($item, $key, $value);
        }
        $this->setSubstringSearchValue($filter, $item);
        $this->setOrder($filter, $item);
        $this->setGetFrom($filter, $item);
        $this->setGetTo($filter, $item);
        $this->setLimitFrom($filter, $item);
        $this->setLimitCount($filter, $item);
        return $item;
    }

    /**
     * @param FilterQueryInterface $filterQuery
     * @param Builder $query
     */
    protected function getFullSearchQuery(FilterQueryInterface $filterQuery, Builder $query)
    {
        if (!$this->metadata->getSupportFullSearch()) {
            return;
        }
        $string = $filterQuery->getFullSearch();
        if (empty($string)) {
            return;
        }
        $fields = array_keys($this->metadata->getData()['fields']);
        $newQuery = $this->newModelQuery();
        foreach ($fields as $field) {
            if ($this->metadata->isFake($field)) {
                continue;
            }
            $newQuery->orWhere($this->table . '.' . $field, 'ILIKE', '%' . $string . '%');
        }
        $query->addNestedWhereQuery($newQuery->getQuery());
    }

    /**
     * Build query by relation tree
     *
     * Make reverse relation model tree and go through it
     * Build ids array for each iteration
     * Make query by ids whereIn
     *
     * @param FilterQueryInterface $filterQuery
     * @param Builder $query
     * @throws ExceptionInterface
     * @throws ReflectionException
     */
    protected function getRelationQuery(FilterQueryInterface $filterQuery, Builder $query)
    {
        if (empty($filterQuery->getRelationModel())) {
            return;
        }
        $tree = $this->getReverseRelationArray([$this]);
        array_shift($tree);
        $ids = $filterQuery->getRelationId();
        while (count($tree)) {
            /** @var Model|QueryBuilder $model */
            $model = array_shift($tree);
            $relation = $model->metadata->getTreeRelation();
            $ids = $this->getRelationIdsByType($relation, $model, $ids);
        }
        $query->whereIn($this->table . '.id', $ids);
    }

    /**
     * @param Builder|Model $item
     * @param string $key
     * @param mixed $value
     */
    protected function setWhereValue($item, string $key, $value): void
    {
        $fields = $this->metadata->getFields();
        if (!isset($fields[$key]) || empty($value) || $this->getRelationWhere($item, $key, $value)) {
            return;
        }
        if (is_array($value)) {
            $nulls = Arr::where($value, function ($value, $key) {
                return strtolower($value) === 'null' || $value === null;
            });
            foreach ($nulls as $null_key => $null_element) {
                unset($value[$null_key]);
            }
            $item->whereIn($this->table . '.' . $key, $value);
            if (isset($nulls) && is_array($nulls) && !empty($nulls) && count($nulls) > 0) {
                $item->orWhereNull($this->table . '.' . $key);
            }
        } elseif (strtolower($value) === 'null') {
            $item->whereNull($this->table . '.' . $key);
        } else {
            $item->where($this->table . '.' . $key, '=', $value);
        }
    }

    /**
     * @param Builder|Model $item
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    protected function getRelationWhere($item, string $key, $value): bool
    {
        $field = $this->metadata->getField($key);
        if (!$field || ($field->getType() !== FieldType::RELATION)
        ) {
            return false;
        }
        $relation = $this->metadata->getRelation($field->getRelation());
        if (!$relation || ($relation->getType() !== RelationType::MANY_TO_MANY)) {
            return false;
        }
        $intermediateMetadata = Session::getMetadata($relation->getIntermediateModel());
        $relationMetadata = Session::getMetadata($relation->getRelationModel());
        $item->whereRaw(sprintf(
                '%s.id IN (select %s from %s where %s in (%s))',
                $this->getTable(),
                Str::singular($this->getTable()) . '_id',
                $intermediateMetadata->getTable(),
                Str::singular($relationMetadata->getTable()) . '_id',
                implode(',', array_map('intval', is_array($value)
                    ? $value
                    : [$value]
                ))
            )
        );
        return true;
    }

    protected function setSubstringSearchValue(FilterQueryInterface $filter, Builder $item): void
    {
        foreach ($filter->getSubstringSearch() as $key => $value) {
            $item->where($this->table . '.' . $key, 'ILIKE', '%' . $value . '%');
        }
    }

    protected function setOrder(FilterQueryInterface $filter, Builder $query): void
    {
        $order = empty($filter->getOrder())
            ? $this->metadata->getDefaultSortBy()
            : $filter->getOrder();
        if (empty($order)) {
            return;
        }

        foreach ($order as $fieldName => $sortOrder) {
            $field = $this->metadata->getField($fieldName);
            if (is_null($field)) {
                continue;
            }
            if (!SortOrder::check(strtolower($sortOrder))) {
                $sortOrder = SortOrder::ASC;
            }

            if ($field->getType() === FieldType::RELATION) {
                $this->setRelationOrder($fieldName, $sortOrder, $field, $query);
                continue;
            }
            $query->orderBy($fieldName, $sortOrder);
        }
    }

    protected function setRelationOrder(string $fieldName, string $sortOrder, FieldInterface $field, Builder $query)
    {
        $relation = $this->metadata->getRelation($field->getRelation());
        $relationType = $relation->getType();
        if (in_array($relationType, [RelationType::ONE_TO_MANY, RelationType::MANY_TO_MANY])) {
            return;
        }
        $relationMetadata = Session::getMetadata($relation->getRelationModel());
        /** @var MetadataInterface $relationMetadata */
        $relationMetadata = new $relationMetadata;
        $relationTable = $relationMetadata->getTable();
        $query->leftjoin(
            $relationTable,
            $this->table . '.' . $fieldName,
            '=',
            $relationTable . '.id'
        )->orderBy($relationTable . '.' . $relationMetadata->getViewName(), $sortOrder);
    }

    protected function setGetFrom(FilterQueryInterface $filter, Builder $item): void
    {
        foreach ($filter->getFrom() as $key => $value) {
            $item->where($this->table . '.' . $key, '>=', $value);
        }
    }

    protected function setGetTo(FilterQueryInterface $filter, Builder $item): void
    {
        foreach ($filter->getTo() as $key => $value) {
            $item->where($this->table . '.' . $key, '<=', $value);
        }
    }

    protected function setLimitFrom(FilterQueryInterface $filter, Builder $item): void
    {
        if (!empty($filter->getLimitFrom())) {
            $item->offset($filter->getLimitFrom());
        }
    }

    protected function setLimitCount(FilterQueryInterface $filter, Builder $item): void
    {
        if (($limit = $filter->getLimitCount()) != 0) {
            $item->limit($limit);
        }
    }

    /**
     * @param Filter[] $tree
     * @return Filter[]
     */
    public function getReverseRelationArray(array $tree)
    {
        $relation = $this->metadata->getTreeRelation();
        if (!empty($relation)) {
            $model = $this->getModelObjectByName($relation->getRelationModel());
            array_unshift($tree, $model);
            $tree = $model->getReverseRelationArray($tree);
        }
        return $tree;
    }

    /**
     * @param RelationInterface $relation
     * @param Model $model
     * @param int[] $ids
     * @return array|int[]|void
     * @throws ReflectionException
     */
    private function getRelationIdsByType(RelationInterface $relation, Model $model, array $ids)
    {
        switch ($relation->getType()) {
            case RelationType::MANY_TO_MANY:
                return $this->getM2MRelationIds($model, $relation, $ids);
            case RelationType::ONE_TO_MANY:
                return $this->getO2MRelationIds($model, $ids);
            default:
                return $this->getOne2OneRelationIds($model, $relation, $ids);
        }
    }

    /**
     * @param Model $model
     * @param RelationInterface $relation
     * @param int[] $ids
     * @return int[]
     * @throws ReflectionException
     */
    protected function getM2MRelationIds(Model $model, RelationInterface $relation, array $ids)
    {
        $intermediateModel = $this->getModelObjectByName($relation->getIntermediateModel());
        $reflectionModel = new ReflectionClass($model);
        $items = $intermediateModel->whereIn(Str::snake($relation->getRelationModel()) . '_id', $ids)->get()->all();
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item->{Str::snake($reflectionModel->getShortName()) . '_id'};
        }
        return $ids;
    }

    /**
     * @param Model|QueryBuilder $model
     * @param int[] $sourceIds
     * @return int[]
     */
    protected function getO2MRelationIds(Model $model, array $sourceIds)
    {
        $ids = [];
        foreach ($model->whereIn('id', $sourceIds)->get($model->table . '_id')->all() as $item) {
            $ids[] = $item->{$model->table . '_id'};
        }
        return array_values(array_unique($ids));
    }

    /**
     * @param Model|QueryBuilder $model
     * @param RelationInterface $relation
     * @param int[] $sourceIds
     * @return int[]
     */
    private function getOne2OneRelationIds(Model $model, RelationInterface $relation, array $sourceIds)
    {
        $ids = [];
        /** @var Model $items */
        $items = $model->whereIn(Str::singular($relation->getRelationTable()) . '_id', $sourceIds)->get(['id'])->all();
        foreach ($items as $item) {
            $ids[] = $item->id;
        }
        return $ids;
    }

}
