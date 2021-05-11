<?php

namespace EgalFramework\Model\Deprecated\Traits\Model;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Common\RelationType;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use EgalFramework\Model\Deprecated\Model;
use EgalFramework\Model\Deprecated\NotFoundException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

/**
 * @deprecated
 */
trait Relation
{

    /**
     * @param int[] $ids
     * @param RelationInterface $relation
     * @return array
     * @throws NotFoundException
     */
    protected function getItemsByRelation(array $ids, RelationInterface $relation): array
    {
        if (empty($ids)) {
            return [];
        }
        switch ($relation->getType()) {
            case RelationType::BELONGS_TO:
                $result = $this->getBTItems($ids, $relation);
                break;
            case RelationType::ONE_TO_ONE:
                $result = $this->getO2OItems($ids, $relation);
                break;
            case RelationType::ONE_TO_MANY:
                $result = $this->getO2MItems($ids, $relation);
                break;
            case RelationType::MANY_TO_MANY:
                $result = $this->getM2MItems($ids, $relation);
                break;
            default:
                throw new NotFoundException('Wrong relation type', 404);
        }
        $items = [];
        if (empty($result['items'])) {
            $result['items'] = [];
        }
        foreach ($result['items'] as $item) {
            $items[$item['id']] = $item;
        }
        return [
            'mapping' => isset($result['mapping'])
                ? $result['mapping']
                : null,
            'items' => $items
        ];
    }

    /**
     * @param int[] $ids
     * @param RelationInterface $relation
     * @return array
     */
    private function getBTItems(array $ids, RelationInterface $relation): array
    {
        /** @var Builder $this */
        /** @var Model[]|Collection $items */
        $items = $this->whereIn('id', $ids)->get();
        $fieldName = Str::singular(Session::getMetadata($relation->getRelationModel())->getTable()) . '_id';
        $idMapping = [];
        foreach ($items as $item) {
            $idMapping[$item->id] = $item->{$fieldName};
        }
        if (empty($idMapping)) {
            return [];
        }
        $modelName = Session::getModelManager()->getModelPath($relation->getRelationModel());
        /** @var Builder $model */
        $model = new $modelName;
        $items = $model->whereIn('id', $idMapping)
            ->limit($this->getMaxRelations())
            ->get();
        return ['mapping' => $idMapping, 'items' => $items->toArray()];
    }

    /**
     * @param int[] $ids
     * @param RelationInterface $relation
     * @return array
     */
    private function getO2OItems(array $ids, RelationInterface $relation): array
    {
        $idFieldName = Str::snake($this->metadata->getFieldByRelation($relation->getName()));
        /** @var Builder|Model $this */
        $items = $this->whereIn('id', $ids)->get($idFieldName)->all();
        if (count($items) == 0) {
            return [];
        }
        $searchIds = [];
        foreach ($items as $item) {
            $searchIds[] = $item->{$idFieldName};
        }
        $modelName = Session::getModelManager()->getModelPath($relation->getRelationModel());
        /** @var Model|Builder $model */
        $model = new $modelName;
        return ['items' => $model->whereIn('id', $searchIds)->limit($this->getMaxRelations())->get()->toArray()];
    }

    /**
     * @param int[] $ids
     * @param RelationInterface $relation
     * @return array
     */
    private function getO2MItems(array $ids, RelationInterface $relation): array
    {
        $modelName = Session::getModelManager()->getModelPath($relation->getRelationModel());
        /** @var Builder|Model $model */
        $model = new $modelName;
        $idFieldName = Str::singular($this->table) . '_id';
        $items = $model->whereIn($idFieldName, $ids)->get()->all();
        if (count($items) == 0) {
            return [];
        }
        $idMapping = [];
        $fieldName = Str::singular($this->table) . '_id';
        /** @var Model $item */
        foreach ($items as $item) {
            if (!isset($idMapping[$item->{$fieldName}])) {
                $idMapping[$item->{$fieldName}] = [];
            }
            $idMapping[$item->{$fieldName}][] = $item->id;
        }
        return ['items' => $items, 'mapping' => $idMapping];
    }

    /**
     * @param int[] $ids
     * @param RelationInterface $relation
     * @return array
     */
    private function getM2MItems(array $ids, RelationInterface $relation): array
    {
        $sourceIdField = Str::singular($this->table) . '_id';
        $intermediateModelName = Session::getModelManager()->getModelPath($relation->getIntermediateModel());
        /** @var Model|Builder $intermediateModel */
        $intermediateModel = new $intermediateModelName;
        $intermediateItems = $intermediateModel
            ->whereIn($sourceIdField, $ids)
            ->limit($this->getMaxRelations())
            ->get()
            ->all();
        if (empty($intermediateItems)) {
            return [];
        }

        $ids = [];
        $idMapping = [];
        $targetFieldName = Str::singular(Session::getMetadata($relation->getRelationModel())->getTable()) . '_id';
        foreach ($intermediateItems as $intermediateItem) {
            if (!isset($idMapping[$intermediateItem->{$sourceIdField}])) {
                $idMapping[$intermediateItem->{$sourceIdField}] = [];
            }
            $idMapping[$intermediateItem->{$sourceIdField}][]
                = $ids[]
                = $intermediateItem->{$targetFieldName};
        }
        $modelName = Session::getModelManager()->getModelPath($relation->getRelationModel());
        /** @var Model|Builder $model */
        $model = new $modelName;
        return ['mapping' => $idMapping, 'items' => $model->whereIn('id', $ids)->get()->toArray()];
    }

    private function getMaxRelations(): int
    {
        $roles = Session::getRegistry()->get('Roles');
        if (!is_array($roles)) {
            return Settings::getDefaultMaxRelations();
        }
        $maxRelations = null;
        foreach ($roles as $role) {
            $roleMaxRelations = Settings::getMaxRelationsByRole($role);
            if (is_null($roleMaxRelations)) {
                continue;
            }
            $maxRelations = ($maxRelations < $roleMaxRelations)
                ? $roleMaxRelations
                : $maxRelations;
        }
        return is_null($maxRelations)
            ? Settings::getDefaultMaxRelations()
            : $maxRelations;
    }

    protected function getM2MFieldsFromArray(array $attributes): array
    {
        $result = [];
        foreach ($attributes as $fieldName => $attribute) {
            $field = $this->metadata->getField($fieldName);
            if (is_null($field) || $field->getType() !== FieldType::RELATION || !is_array($attribute)) {
                continue;
            }
            if ($this->metadata->getRelation($field->getRelation())->getType() !== RelationType::MANY_TO_MANY) {
                continue;
            }
            $result[$fieldName] = array_unique($attribute);
        }
        return $result;
    }

    protected function setUpM2MRelations(array $m2mFields): void
    {
        foreach ($m2mFields as $fieldName => $values) {
            /** @var RelationInterface $relation */
            $relation = $this->metadata->getRelation($this->metadata->getField($fieldName)->getRelation());
            $modelName = Session::getModelManager()->getModelPath($relation->getIntermediateModel());
            /** @var Model|EloquentBuilder $intermediateModel */
            $intermediateModel = new $modelName;
            $modelName = Session::getModelManager()->getModelPath($relation->getRelationModel());
            /** @var Model $relationModel */
            $relationModel = new $modelName;
            foreach ($values as $value) {
                $intermediateModel->firstOrCreate([
                    Str::singular($this->table) . '_id' => $this->id,
                    Str::singular($relationModel->getTable()) . '_id' => $value,
                ]);
            }
            $intermediateModel
                ->where(Str::singular($this->table) . '_id', $this->id)
                ->whereNotIn(Str::singular($relationModel->getTable()) . '_id', $values)
                ->delete();
        }
    }

}
