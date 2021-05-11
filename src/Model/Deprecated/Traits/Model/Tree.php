<?php

namespace EgalFramework\Model\Deprecated\Traits\Model;

use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Common\RelationType;
use EgalFramework\Model\Deprecated\Model;
use EgalFramework\Model\Deprecated\NotFoundException;
use EgalFramework\Model\Deprecated\Tree as ModelTree;
use EgalFramework\Model\Deprecated\ValidateException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

/**
 * @deprecated
 */
trait Tree
{

    /**
     * Builds tree by relations
     * @return array
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws ValidateException
     */
    public function getTree()
    {
        if (!$this->metadata->getShowTree()) {
            throw new ValidateException('Method is not allowed', 401);
        }
        $tree = new ModelTree;
        $this->buildTree($tree, (new ReflectionClass($this))->getShortName());
        return $tree->toArray();
    }

    /**
     * Recursive build tree level for a model
     * @param ModelTree $tree
     * @param string $modelName
     * @param string $fieldName
     * @param int $id
     * @throws NotFoundException
     */
    protected function buildTree(ModelTree $tree, string $modelName, string $fieldName = 'id', int $id = 0)
    {
        $model = $this->getModelObjectByName($modelName);
        /** @var Model[] $items */
        $items = empty($id)
            ? $model->get()->all()
            : $model->where($fieldName, $id)->get()->all();
        $relation = $model->metadata->getTreeRelation();
        foreach ($items as $item) {
            $subTree = $tree->add($item->id, $item->{$model->metadata->getViewName()});
            if (!is_null($direction = $item->metadata->getTreeDirection())) {
                $subTree->setDirection($direction->setId($item->id));
            }
            if (is_null($relation)) {
                continue;
            }
            $this->routeTreeByType($relation, $subTree, $modelName, $item);
        }
    }

    /**
     * Route tree building in case of relation type
     * @param RelationInterface $relation Tree relation of the current item
     * @param ModelTree $tree Current processing tree
     * @param string $modelName Model name for current tree object
     * @param Model $item Current processing item
     * @throws NotFoundException
     */
    protected function routeTreeByType(RelationInterface $relation, ModelTree $tree, string $modelName, Model $item)
    {
        switch ($relation->getType()) {
            case RelationType::MANY_TO_MANY:
                $this->buildMTMTree($tree, $relation, Str::snake($modelName) . '_id', $item->id);
                break;
            case RelationType::ONE_TO_MANY:
                $this->buildTree($tree, $relation->getRelationModel(), Str::snake($modelName) . '_id', $item->id);
                break;
            default:
                if (is_null($item->{Str::singular($relation->getRelationTable()) . '_id'})) {
                    break;
                }
                $this->buildTree(
                    $tree,
                    $relation->getRelationModel(),
                    'id',
                    $item->{Str::singular($relation->getRelationTable()) . '_id'}
                );
        }
    }

    /**
     * Build tree by many-to-many relation
     * @param ModelTree $tree
     * @param RelationInterface $relation
     * @param string $fieldName
     * @param int $id
     * @throws NotFoundException
     */
    protected function buildMTMTree(ModelTree $tree, RelationInterface $relation, string $fieldName, int $id)
    {
        $intermediateModel = self::getModelObjectByName($relation->getIntermediateModel());
        /** @var Model[] $items Intermediate items */
        $items = $intermediateModel->where($fieldName, $id)->get()->all();
        $treeField = Str::singular($relation->getRelationTable()) . '_id';
        $innerClassName = $this->modelManager->getModelPath($relation->getRelationModel());
        foreach ($items as $item) {
            /** @var QueryBuilder $innerClassName */
            /** @var Model $innerItem */
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $innerItem = $innerClassName::where('id', $item->{$treeField})->first();
            if (!$innerItem) {
                continue;
            }
            $subTree = $tree->add($innerItem->id, $innerItem->{$innerItem->metadata->getViewName()});
            if (!is_null($direction = $innerItem->metadata->getTreeDirection())) {
                $subTree->setDirection($direction->setId($innerItem->id));
            }
            $subTreeRelation = $innerItem->metadata->getTreeRelation();
            if (is_null($subTreeRelation)) {
                continue;
            }
            $this->routeTreeByType($subTreeRelation, $subTree, $subTreeRelation->getRelationModel(), $innerItem);
        }
    }

}
