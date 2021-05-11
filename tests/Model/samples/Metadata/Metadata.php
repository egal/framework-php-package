<?php

namespace EgalFramework\Model\Tests\Samples\Metadata;

use EgalFramework\Common\FieldType;
use EgalFramework\Common\Interfaces\FieldInterface;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\RelationDirectionInterface;
use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Model\Tests\Samples\Stubs\Direction;
use EgalFramework\Model\Tests\Samples\Stubs\Field;
use EgalFramework\Model\Tests\Samples\Stubs\Relation;
use Exception;

class Metadata implements MetadataInterface
{

    /** @var Field[] */
    protected array $data;

    protected bool $supportFullSearch = false;

    protected bool $openByRelation = false;

    protected string $label = '';

    protected string $viewName = '';

    protected array $relations = [];

    /** @var bool */
    protected bool $showTree = false;

    protected string $table = '';

    public function isFake(string $name): bool
    {
        return $this->data[$name]->getType() === FieldType::FAKE;
    }

    public function getFieldNames(bool $skipFieldNames = true): array
    {
        if (!$skipFieldNames) {
            return array_keys($this->data);
        }
        $data = [];
        foreach (array_keys($this->data) as $field) {
            if (!$this->isFake($field)) {
                $data[] = $field;
            }
        }
        return $data;
    }

    public function getValidationRules(bool $skipRequired): array
    {
        return $skipRequired
            ? []
            : ['content'];
    }

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
        $fields = [];
        if (!isset($this->data) || !is_array($this->data)) {
            throw new Exception('There is no MetaData for ' . static::class, 400);
        }
        foreach ($this->data as $key => $value) {
            $fields[$key] = $value->toArray();
        }
        $result = [
            'label' => $this->label,
            'viewName' => $this->viewName,
            'supportFullSearch' => $this->supportFullSearch,
            'openByRelation' => $this->openByRelation,
            'fields' => $fields,
        ];
        if (isset($this->treeRelation) && !is_null($this->treeRelation)) {
            $result['treeRelation'] = $this->treeRelation->toArray();
        }
        if (!empty($this->relations)) {
            $result['relations'] = [];
            foreach ($this->relations as $model => $relation) {
                $result['relations'][$model] = $relation->toArray();
            }
        }
        if (!empty($this->filterFields)) {
            $result['filterFields'] = [];
            foreach ($this->filterFields as $filterField) {
                $result['filterFields'][] = $filterField->toArray();
            }
        }
        return $result;
    }

    public function getTreeRelation(): RelationInterface
    {
        return new Relation('', '');
    }

    public function getTreeDirection(): RelationDirectionInterface
    {
        return new Direction;
    }

    public function getViewName(): string
    {
        return $this->viewName;
    }

    public function getMigration(): array
    {
    }

    public function getFields(): array
    {
        return $this->data;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getRelation(string $name): ?RelationInterface
    {
        // TODO: Implement getRelation() method.
    }

    public function getFactoryFields(): string
    {
        // TODO: Implement getFactoryFields() method.
    }

    public function getField(string $name): ?FieldInterface
    {
        return $this->data[$name];
    }

    public function getFieldByRelation(string $relation): ?string
    {
        // TODO: Implement getFieldByRelation() method.
    }

    public function getShowTree(): bool
    {
        return $this->showTree;
    }

    public function getDefaultSortBy(): ?array
    {
        // TODO: Implement getDefaultSortBy() method.
    }

    public function getRelations(): array
    {
        // TODO: Implement getRelations() method.
    }

    public function getDefaultMaxCount(): ?int
    {
        // TODO: Implement getDefaultMaxCount() method.
    }

    public function getDefaultCount(): ?int
    {
        // TODO: Implement getDefaultCount() method.
    }

    public function getHiddenFields(): array
    {
        // TODO: Implement getHiddenFields() method.
    }
}
