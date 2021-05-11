<?php

namespace EgalFramework\Metadata;

use EgalFramework\Common\Interfaces\RelationInterface;
use EgalFramework\Common\Session;

/**
 * Class Relation
 * @package EgalFramework\Metadata
 */
class Relation implements RelationInterface
{

    private string $type;

    private string $relationModel;

    private string $intermediateModel;

    private string $name;

    private bool $checkOnDelete;

    /**
     * Relation constructor.
     * @param string $type
     * @param string $relationModel
     * @param string $intermediateModel
     */
    public function __construct(string $type, string $relationModel, string $intermediateModel = '')
    {
        $this->type = $type;
        $this->relationModel = $relationModel;
        $this->intermediateModel = $intermediateModel;
    }

    public function toArray(): array
    {
        return array_filter([
            'type' => $this->type,
            'relationModel' => $this->relationModel,
            'intermediateModel' => $this->intermediateModel,
        ]);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getRelationModel(): string
    {
        return $this->relationModel;
    }

    /**
     * @return string
     */
    public function getIntermediateModel(): string
    {
        return $this->intermediateModel;
    }

    /**
     * @return string
     */
    public function getRelationTable(): string
    {
        return Session::getMetadata($this->relationModel)->getTable();
    }

    /**
     * @return string
     */
    public function getIntermediateTable()
    {
        return Session::getMetadata($this->intermediateModel)->getTable();
    }

    public function setName(string $name): RelationInterface
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setCheckOnDelete(bool $checkOnDelete): RelationInterface
    {
        $this->checkOnDelete = $checkOnDelete;
        return $this;
    }

    public function getCheckOnDelete(): bool
    {
        return isset($this->checkOnDelete)
            ? $this->checkOnDelete
            : false;
    }

}
