<?php

namespace Egal\Core\Database\Metadata;

class Relation
{
    private string $name;
    private Model $model;

    public static function make(string $name, string $related): self
    {
        $relation = new self();
        $relation->setName($name);
        $relation->setModel((new $related())->getMetadata());

        return $relation;
    }

    private function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    private function setModel(Model $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'model_class' => $this->getModel()->getClass()
        ];
    }
}
