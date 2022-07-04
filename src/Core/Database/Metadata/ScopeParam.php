<?php

namespace Egal\Core\Database\Metadata;

class ScopeParam
{
    private string $name;
    private DataType $type;

    public static function make(string $name, DataType $type)
    {
        $scopeParam = new self();
        $scopeParam->setName($name);
        $scopeParam->setType($type);

        return $scopeParam;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): DataType
    {
        return $this->type;
    }

    public function setType(DataType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function toArray()
    {
        return [
          'name' => $this->getName(),
          'type' => $this->getType()->value
        ];
    }
}
