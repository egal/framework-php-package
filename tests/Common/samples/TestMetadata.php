<?php

namespace EgalFramework\Common\Tests\Samples;

use EgalFramework\Common\Interfaces\FieldInterface;
use EgalFramework\Common\Interfaces\MetadataInterface;
use EgalFramework\Common\Interfaces\RelationDirectionInterface;
use EgalFramework\Common\Interfaces\RelationInterface;

class TestMetadata implements MetadataInterface
{

    public function isFake(string $name): bool
    {
    }

    public function getFieldNames(bool $skipFake = true): array
    {
    }

    public function getValidationRules(bool $skipRequired): array
    {
    }

    public function getSupportFullSearch(): bool
    {
    }

    public function getData(): array
    {
    }

    public function getTreeRelation(): ?RelationInterface
    {
    }

    public function getTreeDirection(): ?RelationDirectionInterface
    {
    }

    public function getViewName(): string
    {
    }

    public function getMigration(): array
    {
    }

    public function getFields(): array
    {
    }

    public function getTable(): string
    {
        // TODO: Implement getTable() method.
    }

    public function getField(string $name): ?FieldInterface
    {
        // TODO: Implement getField() method.
    }

    public function getFieldByRelation(string $relation): ?string
    {
        // TODO: Implement getFieldByRelation() method.
    }

    public function getRelation(string $name): ?RelationInterface
    {
        // TODO: Implement getRelation() method.
    }

    public function getFactoryFields(): string
    {
        // TODO: Implement getFactoryFields() method.
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
