<?php

namespace EgalFramework\Model\Tests\Samples\Stubs;

use EgalFramework\Common\Interfaces\RelationInterface;

class Relation implements RelationInterface
{

    private string $type;

    private string $model;

    public function __construct(string $type, string $model)
    {
        $this->type = $type;
        $this->model = $model;
    }

    public function getType(): string
    {
        return '';
    }

    public function getTypeNameM2M(): string
    {
        return '';
    }

    public function getIntermediateModel(): string
    {
        return '';
    }

    public function getRelationModel(): string
    {
        return '';
    }

    public function getRelationTable(): string
    {
        return '';
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'relationModel' => $this->model,
        ]);
    }

    public function setName(string $name): RelationInterface
    {
        // TODO: Implement setName() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function setCheckOnDelete(bool $checkOnDelete): RelationInterface
    {
        // TODO: Implement setCheckOnDelete() method.
    }

    public function getCheckOnDelete(): bool
    {
        // TODO: Implement getCheckOnDelete() method.
    }
}
