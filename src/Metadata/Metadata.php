<?php

namespace EgalFramework\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\FieldInterface;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\RelationDirectionInterface;
use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Common\RelationType;
use EgalFramework\Common\Session;

/**
 * Class AbstractMetaData
 * @package App\MetaData
 */
class Metadata implements MetadataInterface
{

    /** @var string */
    protected string $label;

    /** @var Field[] */
    protected array $data;

    /** @var string[] */
    protected array $fakeFields;

    /** @var string[] */
    protected array $relationToField;

    /** @var string */
    protected string $table;

    /** @var string */
    protected string $viewName = 'name';

    /** @var bool */
    protected bool $supportFullSearch = false;

    /** @var bool */
    protected bool $openByRelation = false;

    /** @var Relation */
    protected Relation $treeRelation;

    /** @var RelationDirection */
    protected RelationDirection $treeDirection;

    /** @var Relation[] */
    protected array $relations;

    /** @var FilterField[] Fields will be shown in filter */
    protected array $filterFields;

    /** @var bool */
    protected bool $showTree = false;

    protected array $defaultSortBy;

    protected int $defaultMaxCount;

    protected int $defaultCount;

    /** @var string[] This fields will not be shown for a user */
    protected array $hiddenFields;

    /**
     * AbstractMetaData constructor.
     */
    public function __construct()
    {
        $this->fakeFields = [];
        if (!isset($this->relations)) {
            $this->relations = [];
        }
        $this->relationToField = [];
        if (!isset($this->hiddenFields)) {
            $this->hiddenFields = [];
        }
        foreach ($this->data as $key => $field) {
            if ($field->getType() === FieldType::RELATION) {
                $this->relationToField[$field->getRelation()] = $key;
            }
            if ($field->getType() == FieldType::FAKE) {
                $this->fakeFields[] = $key;
            } elseif ($this->isFakeFieldRelation($field)) {
                $this->fakeFields[] = $key;
                $this->setFakeFieldRelationReadonly($field);
            }
        }
        foreach ($this->relations as $relationName => $relation) {
            $relation->setName($relationName);
        }
    }

    private function isFakeFieldRelation(FieldInterface $field): bool
    {
        if ($field->getType() == FieldType::RELATION) {
            $relationName = $field->getRelation();
            return isset($this->relations[$relationName])
                && in_array(
                    $this->relations[$relationName]->getType(),
                    [RelationType::ONE_TO_MANY, RelationType::MANY_TO_MANY]
                );
        }
        return false;
    }

    private function setFakeFieldRelationReadonly(FieldInterface $field): void
    {
        if ($this->relations[$field->getRelation()] == RelationType::ONE_TO_MANY) {
            $field->setReadonly(true);
        }
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $fieldName
     * @return bool
     */
    public function isFake(string $fieldName): bool
    {
        return in_array($fieldName, $this->fakeFields);
    }

    public function getFieldByRelation(string $relation): ?string
    {
        return isset($this->relationToField[$relation])
            ? $this->relationToField[$relation]
            : null;
    }

    /**
     * @return bool
     */
    public function getSupportFullSearch(): bool
    {
        return $this->supportFullSearch;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getData(): array
    {
        if (!isset($this->data) || !is_array($this->data)) {
            throw new Exception('There is no MetaData for ' . static::class, 400);
        }
        $result = [
            'label' => $this->label,
            'viewName' => $this->viewName,
            'supportFullSearch' => $this->supportFullSearch,
            'openByRelation' => $this->openByRelation,
            'fields' => $this->getFieldsArray(),
        ];
        if (isset($this->defaultSortBy)) {
            $result['defaultSortBy'] = $this->defaultSortBy;
        }
        if (isset($this->treeRelation) && !is_null($this->treeRelation)) {
            $result['treeRelation'] = $this->treeRelation->toArray();
        }
        if (!empty($this->relations)) {
            $result['relations'] = [];
            foreach ($this->relations as $relationName => $relation) {
                $result['relations'][$relationName] = $relation->toArray();
            }
        }
        if (!empty($this->filterFields)) {
            $result['filterFields'] = [];
            foreach ($this->filterFields as $fieldName => $filterField) {
                $result['filterFields'][$fieldName] = $filterField->toArray();
            }
        }
        return $result;
    }

    protected function getFieldsArray(): array
    {
        $fields = [];
        foreach ($this->data as $key => $value) {
            if ($value->getHideFromUser()) {
                continue;
            }
            $fields[$key] = $value->toArray();
        }
        return $fields;
    }

    /**
     * @param bool $skipRequired
     * @return array
     * @throws Exception
     */
    public function getValidationRules(bool $skipRequired): array
    {
        $rules = [];
        foreach ($this->data as $key => $field) {
            if ($field->getType() == FieldType::RELATION) {
                $this->checkRelationExists($field->getRelation());
                $rules[$key] = $field->getAllValidationRules(
                    $this->table, $key, $skipRequired, $this->relations[$field->getRelation()]
                );
            } else {
                $rules[$key] = $field->getAllValidationRules($this->table, $key, $skipRequired);
            }
        }
        return array_filter($rules);
    }

    /**
     * @param string $modelName
     * @throws Exception
     */
    private function checkRelationExists(string $modelName)
    {
        if (!isset($this->relations[$modelName])) {
            throw new Exception('Relation for ' . $modelName . ' does not exist');
        }
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function getMigration(): array
    {
        $result = [];
        foreach ($this->data as $key => $field) {
            $result[] = $field->getMigration($key);
            if ($field->getType() !== FieldType::RELATION) {
                continue;
            }
            if (!isset($this->relations[$field->getRelation()])) {
                throw new Exception(sprintf('Relation "%s" not found', $field->getRelation()), 404);
            }
            $relation = $this->relations[$field->getRelation()];
            if (in_array($relation->getType(), [RelationType::ONE_TO_MANY, RelationType::MANY_TO_MANY])) {
                continue;
            }
            $result[] = '// @TODO if this is an intermediate table, there may be need to add'
                . ' cascadeOnDelete() method call';
            $result[] = sprintf(
                '$table->foreign(\'%s\')->on(\'%s\')->references(\'id\');',
                $key,
                Session::getMetadata($relation->getRelationModel())->getTable()
            );
        }
        return array_filter($result);
    }

    /**
     * @param bool $skipFake
     * @return string[]
     */
    public function getFieldNames(bool $skipFake = true): array
    {
        $result = [];
        foreach (array_keys($this->data) as $key) {
            if ($skipFake && $this->isFake($key)) {
                continue;
            }
            $result[] = $key;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getViewName(): string
    {
        return $this->viewName;
    }

    /**
     * @return RelationInterface
     */
    public function getTreeRelation(): ?RelationInterface
    {
        return isset($this->treeRelation)
            ? $this->treeRelation
            : null;
    }

    /**
     * @return RelationDirection
     */
    public function getTreeDirection(): ?RelationDirectionInterface
    {
        return isset($this->treeDirection)
            ? $this->treeDirection
            : null;
    }

    public function getField(string $name): ?Field
    {
        return empty($this->data[$name])
            ? null
            : $this->data[$name];
    }

    /**
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->data;
    }

    public function getRelation(string $name): ?RelationInterface
    {
        return isset($this->relations[$name])
            ? $this->relations[$name]
            : null;
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getFactoryFields(): string
    {
        $fields = [];
        foreach ($this->data as $key => $field) {
            $fieldStr = '\'' . $key . '\' => ';
            switch ($field->getType()) {
                case FieldType::BOOL:
                    $fieldStr .= '$this->faker->boolean';
                    break;
                case FieldType::DATE:
                    $fieldStr .= '$this->faker->date(\'Y-m-d\')';
                    break;
                case FieldType::DATETIME:
                    $fieldStr .= '$this->faker->dateTime->format(\'Y-m-d H:i:s\')';
                    break;
                case FieldType::TIME:
                    $fieldStr .= '$this->faker->time()';
                    break;
                case FieldType::EMAIL:
                    $fieldStr .= '$this->faker->email';
                    break;
                case FieldType::FLOAT:
                    $fieldStr .= '$this->faker->randomFloat()';
                    break;
                case FieldType::INT:
                    $fieldStr .= '$this->faker->randomNumber()';
                    break;
                case FieldType::PASSWORD:
                    $fieldStr .= '$this->faker->password';
                    break;
                case FieldType::RELATION:
                    if (!isset($this->relations[$field->getRelation()])) {
                        throw  new Exception(sprintf('Relation "%s" not found', $field->getRelation()), 404);
                    }
                    $fieldStr .= '(\\App\\PublicModels\\'
                        . $this->relations[$field->getRelation()]->getRelationModel()
                        . '::all()->random(1)->first())->id';
                    break;
                case FieldType::STRING:
                    $fieldStr .= '$this->faker->unique()->sentence';
                    break;
                case FieldType::TEXT:
                    $fieldStr .= '$this->faker->text';
                    break;
                case FieldType::JSON:
                    $fieldStr .= '\'{}\'';
                    break;
                default:
                    continue 2;
            }
            $fields[] = str_repeat(' ', 8) . $fieldStr . ',';
        }
        return implode(PHP_EOL, $fields);
    }

    public function getShowTree(): bool
    {
        return $this->showTree;
    }

    public function getDefaultSortBy(): ?array
    {
        return isset($this->defaultSortBy)
            ? $this->defaultSortBy
            : null;
    }

    /**
     * @return RelationInterface[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

    public function getDefaultMaxCount(): ?int
    {
        return isset($this->defaultMaxCount)
            ? $this->defaultMaxCount
            : null;
    }

    public function getDefaultCount(): ?int
    {
        return isset($this->defaultCount)
            ? $this->defaultCount
            : null;
    }

    /**
     * @return string[]
     */
    public function getHiddenFields(): array
    {
        return $this->hiddenFields;
    }

}
