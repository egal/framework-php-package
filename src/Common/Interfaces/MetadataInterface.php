<?php

namespace EgalFramework\Common\Interfaces;

interface MetadataInterface
{

    public function getTable(): string;

    public function isFake(string $name): bool;

    /**
     * @param bool $skipFake
     * @return string[]
     */
    public function getFieldNames(bool $skipFake = true): array;

    /**
     * @param bool $skipRequired
     * @return string[]
     */
    public function getValidationRules(bool $skipRequired): array;

    public function getSupportFullSearch(): bool;

    public function getData(): array;

    public function getTreeRelation(): ?RelationInterface;

    public function getTreeDirection(): ?RelationDirectionInterface;

    public function getViewName(): string;

    public function getMigration(): array;

    public function getField(string $name): ?FieldInterface;

    public function getFieldByRelation(string $relation): ?string;

    /**
     * @return FieldInterface[]
     */
    public function getFields(): array;

    public function getRelation(string $name): ?RelationInterface;

    public function getFactoryFields(): string;

    public function getShowTree(): bool;

    public function getDefaultSortBy(): ?array;

    /**
     * @return RelationInterface[]
     */
    public function getRelations(): array;

    public function getDefaultMaxCount(): ?int;

    public function getDefaultCount(): ?int;

    public function getHiddenFields(): array;

}
