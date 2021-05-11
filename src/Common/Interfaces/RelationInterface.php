<?php

namespace EgalFramework\Common\Interfaces;

interface RelationInterface
{

    public function getType(): string;

    public function getIntermediateModel(): string;

    public function getRelationModel(): string;

    public function getRelationTable(): string;

    public function toArray(): array;

    public function setName(string $name): self;

    public function getName(): string;

    public function setCheckOnDelete(bool $checkOnDelete): self;

    public function getCheckOnDelete(): bool;

}
