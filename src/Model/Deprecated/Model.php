<?php

namespace EgalFramework\Model\Deprecated;

use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\ModelInterface;
use EgalFramework\Common\Interfaces\ModelManagerInterface;
use EgalFramework\Common\RelationType;
use EgalFramework\Common\Session;
use EgalFramework\Model\Deprecated\Traits\Model\Filter as TraitFilter;
use EgalFramework\Model\Deprecated\Traits\Model\Relation as TraitRelation;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EModel;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;
use EgalFramework\Model\Deprecated\Traits\Model\Tree as TraitTree;

/**
 * @deprecated
 *
 * Class Model
 * @package EgalFramework\Model\Deprecated
 *
 * @property int $id
 * @property string $hash
 *
 * @method static mixed find(int $id)
 */
class Model extends EModel implements ModelInterface
{

    use TraitTree, TraitFilter, TraitRelation;

    protected ModelManagerInterface $modelManager;

    protected MetadataInterface $metadata;

    protected string $className;

    protected $dates = [
        'created_at', 'updated_at',
    ];

    /** @var string */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Model constructor.
     * @param array $attributes
     * @codeCoverageIgnore
     * @throws ReflectionException
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->modelManager = Session::getModelManager();
        $this->className = (new ReflectionClass($this))->getShortName();
        $this->metadata = Session::getMetadata($this->className);
        $this->table = $this->metadata->getTable();
    }

    /**
     * @param array $attributes
     * @return mixed
     * @throws ValidateException
     */
    public function create(array $attributes = [])
    {
        $item = clone $this;
        $m2mFields = $this->getM2MFieldsFromArray($attributes);
        $attributes = $item->updateFieldsWithMetadata($attributes);
        $item->fill($attributes);
        $this->syncChanges();
        $item->checkData(array_merge($attributes, $m2mFields));
        /** @noinspection PhpUndefinedMethodInspection */
        $result = parent::create($item->toArray());
        $this->id = $result->id;
        $this->setUpM2MRelations($m2mFields);
        $this->modelManager->flushCache($this->className, (int)$this->id);
        return $result;
    }

    protected function updateFieldsWithMetadata(array $attributes, bool $isUpdate = false): array
    {
        foreach (array_keys($attributes) as $fieldName) {
            if ($this->metadata->isFake($fieldName)) {
                unset($attributes[$fieldName]);
                continue;
            }
            $field = $this->metadata->getField($fieldName);
            if (is_null($field)) {
                continue;
            }
            if (!$field->getReadonly()) {
                $field->setReadonly(
                    ($isUpdate && $field->getReadonlyOnChange())
                    || (!$isUpdate && $field->getReadonlyOnCreate())
                );
            }
            if ($field->getReadonly()) {
                unset($attributes[$fieldName]);
            }
        }
        return $attributes;
    }

    /**
     * @param array $attributes
     * @param array $options
     * @return bool
     * @throws ValidateException
     */
    public function update(array $attributes = [], array $options = [])
    {
        $m2mFields = $this->getM2MFieldsFromArray($attributes);
        $attributes = $this->updateFieldsWithMetadata($attributes, true);
        $this->fill($attributes);
        $this->syncChanges();
        $attributes = $this->getChanges();
        $this->checkData(array_merge($attributes, $m2mFields), true);
        $result = parent::update($this->updateFieldsWithMetadata($attributes, true), $options);
        $this->setUpM2MRelations($m2mFields);
        $this->modelManager->flushCache($this->className, (int)$this->id);
        return $result;
    }

    /**
     * @param array $attributes
     * @param array $options
     * @throws ValidateException
     */
    public function updateByIds(array $attributes = [], array $options = [])
    {
        if (!isset($attributes['ids'])) {
            throw  new ValidateException('No ids specified');
        }
        $ids = $attributes['ids'];
        if (!is_array($ids)) {
            throw new ValidateException('Specified ids are not an array');
        }
        unset($attributes['ids']);
        /** @var Model[] $items */
        /** @noinspection PhpUndefinedMethodInspection */
        $items = $this->whereIn('id', $ids)->get()->all();
        foreach ($items as $item) {
            $item->update($attributes, $options);
        }
    }

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (is_null($this->metadata->getField('hash'))) {
            return parent::save($options);
        }
        $this->hash = '';
        $result = parent::save($options);
        /** @var Model $fromDb */
        $fromDb = $this->find($this->id);
        $this->attributes = $fromDb->attributes;
        $this->hash = $this->makeHash();
        $this->getConnection()->table($this->getTable())
            ->where('id', $this->id)
            ->update(['hash' => $this->hash]);
        return $result;
    }

    /**
     * @return string
     */
    public function makeHash()
    {
        $arr = array_flip($this->metadata->getFieldNames());
        foreach ($this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable()) as $field) {
            if (isset($arr[$field]) && !empty($this->{$field})) {
                $arr[$field] = $this->{$field};
            } else {
                unset($arr[$field]);
            }
        }
        unset($arr['hash'], $arr['updated_at'], $arr['created_at']);
        ksort($arr);
        return hash('SHA256', json_encode($arr));
    }

    /**
     * @return bool|null
     * @throws Exception
     */
    public function delete()
    {
        $sql = [];
        foreach ($this->metadata->getRelations() as $relation) {
            if (!$relation->getCheckOnDelete()) {
                continue;
            }
            switch ($relation->getType()) {
                case RelationType::MANY_TO_MANY:
                    $metadata = Session::getMetadata($relation->getIntermediateModel());
                    break;
                case RelationType::ONE_TO_MANY:
                case RelationType::ONE_TO_ONE:
                    $metadata = Session::getMetadata($relation->getRelationModel());
                    break;
                default:
                    continue 2;
            }
            $sql[] = sprintf(
                '(SELECT COUNT(*) FROM %s WHERE %s = %d) > 0',
                $metadata->getTable(),
                Str::singular($this->metadata->getTable()) . '_id',
                $this->id
            );
        }
        if (!empty($sql) && DB::select('SELECT ' . implode(' OR ', $sql) . ' AS true')[0]->true) {
            throw new Exception(
                sprintf(
                    'Entity %s with id %d is used in another tables and can\'t be deleted',
                    $this->className, $this->id
                ),
                409
            );
        }
        $result = parent::delete();
        $this->modelManager->flushCache($this->className, (int)$this->id);
        return $result;
    }

    /**
     * @param array $ids
     * @throws Exception
     */
    public function deleteByIds(array $ids)
    {
        /** @var Model[] $items */
        /** @noinspection PhpUndefinedMethodInspection */
        $items = $this->whereIn('id', $ids)->get()->all();
        foreach ($items as $item) {
            $item->delete();
        }
    }

    /**
     * Check if the action exists and supplied data filled right
     * @param array $attributes
     * @param bool $skipRequired
     * @throws ValidateException
     */
    protected function checkData(array $attributes, bool $skipRequired = false)
    {
        $validateCallback = Session::getValidateCallback();
        $errors = $validateCallback($attributes, $this->metadata->getValidationRules($skipRequired));
        if ($errors) {
            $exception = new ValidateException('Failed to validate', 400);
            $exception->setErrors($errors);
            throw $exception;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getItems()
    {
        $query = $this->prepareFilteredQuery();
        $items = $query->get($this->table . '.*');
        $ids = [];
        foreach ($items as $item) {
            $item->validate();
            foreach ($this->metadata->getHiddenFields() as $key) {
                unset($item[$key]);
            }
            $ids[] = $item->id;
        }
        $resultInnerItems = $this->fillItemsRelations($ids, $items);
        $result = [
            'items' => $items->toArray(),
            'count' => $query->limit(-1)->offset(0)->count(),
        ];
        if (!empty($resultInnerItems)) {
            $result['relations'] = $resultInnerItems;
        }
        return $result;
    }

    /**
     * @param int[] $ids
     * @param Collection $items
     * @return array Relations array
     * @throws NotFoundException
     */
    protected function fillItemsRelations(array $ids, Collection $items): array
    {
        $innerItems = $this->getWithItemsByQuery($ids);
        $resultInnerItems = [];
        foreach ($innerItems as $relation => $result) {
            $resultInnerItems[$relation] = empty($result['items'])
                ? []
                : $result['items'];
            if (empty($result['mapping'])) {
                continue;
            }
            foreach ($items as $item) {
                $this->fillItemRelationsFromMapping($result['mapping'], $relation, $item);
            }
        }
        return $resultInnerItems;
    }

    /**
     * @param int[] $ids
     * @return Model[]
     * @throws NotFoundException
     */
    protected function getWithItemsByQuery(array $ids): array
    {
        $filterQuery = Session::getFilterQuery();
        $filterQuery->setQuery(Session::getMessage()->getQuery());
        return $this->getWith($ids, $filterQuery->getWith());
    }

    protected function fillItemRelationsFromMapping(array $mapping, string $relation, Model $item): void
    {
        if (
            isset($mapping[$item->id])
            && ($fieldName = $this->metadata->getFieldByRelation($relation))
        ) {
            $item->{$fieldName} = $mapping[$item->id];
        }
    }

    /**
     * @return array
     * @throws NotFoundException
     */
    public function getItem()
    {
        /** @var QueryBuilder|Model $this */
        $item = $this->find(Session::getMessage()->getId());
        if (!$item) {
            throw new NotFoundException('Item not found', 404);
        }
        $item->validate();
        $innerItems = $this->getWithItemsByQuery([$item->id]);
        $resultInnerItems = [];
        foreach ($innerItems as $relation => $result) {
            $resultInnerItems[$relation] = $result['items'];
            if (!empty($result['mapping'])) {
                $this->fillItemRelationsFromMapping($result['mapping'], $relation, $item);
            }
        }
        $item = $item->toArray();
        foreach ($this->metadata->getHiddenFields() as $key) {
            unset($item[$key]);
        }
        if (!empty($resultInnerItems)) {
            $item['relations'] = $resultInnerItems;
        }
        return $item;
    }

    /**
     * @param int[] $ids
     * @param array $with
     * @return Model[]
     * @throws NotFoundException
     */
    protected function getWith(array $ids, array $with): array
    {
        $result = [];
        foreach ($with as $relationName) {
            $relation = $this->metadata->getRelation($relationName);
            if (is_null($relation)) {
                throw new NotFoundException(sprintf('Relation "%s" not found', $relationName), 404);
            }
            $result[$relationName] = $this->getItemsByRelation($ids, $relation);
        }
        return $result;
    }

    /**
     * @throws HashValidateException
     */
    protected function validate()
    {
        if (is_null($this->metadata->getField('hash'))) {
            return;
        }
        if ($this->hash != $this->makeHash()) {
            throw new HashValidateException(
                'Hash check failed for item ' . get_class($this) . ' #' . $this->{$this->primaryKey}
            );
        }
    }

    /**
     * @param string $name
     * @return Model|QueryBuilder
     */
    public static function getModelObjectByName(string $name)
    {
        $className = Session::getModelManager()->getModelPath($name);
        return new $className;
    }

}
