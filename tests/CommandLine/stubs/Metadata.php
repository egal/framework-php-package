<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use EgalFramework\Common\Interfaces\FieldInterface;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\RelationDirectionInterface;
use EgalFramework\Common\Interfaces\RelationInterface;

class Metadata implements MetadataInterface
{

    protected array $data;

    protected string $table;

    public function __construct()
    {
    }

    public function isFake(string $name): bool
    {
        // TODO: Implement isFake() method.
    }

    public function getFieldNames(bool $skipFake = true): array
    {
        // TODO: Implement getFieldNames() method.
    }

    public function getValidationRules(bool $skipRequired): array
    {
        // TODO: Implement getValidationRules() method.
    }

    public function getSupportFullSearch(): bool
    {
        // TODO: Implement getSupportFullSearch() method.
    }

    public function getData(): array
    {
        // TODO: Implement getData() method.
    }

    public function getTreeRelation(): ?RelationInterface
    {
        // TODO: Implement getTreeRelation() method.
    }

    public function getTreeDirection(): ?RelationDirectionInterface
    {
        // TODO: Implement getTreeDirection() method.
    }

    public function getViewName(): string
    {
        // TODO: Implement getViewName() method.
    }

    public function getMigration(): array
    {
        return ['testField'];
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
    }

    public function getFactoryFields(): string
    {
        return '        \'There are factory fields :)\',';
    }

    public function getField(string $name): ?FieldInterface
    {
        // TODO: Implement getField() method.
    }

    public function getFieldByRelation(string $relation): ?string
    {
        // TODO: Implement getFieldByRelation() method.
    }

    public function getShowTree(): bool
    {
        // TODO: Implement getShowTree() method.
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
