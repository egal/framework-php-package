<?php

namespace Egal\Core\Database\Metadata;

use Egal\Core\Exceptions\MetadataException;

class Scope
{
    private string $name;
    private array $params;

    public static function make(string $name, array $params = []): self
    {
        $field = new self();
        $field->setName($name);
        $field->setParams($params);

        return $field;
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

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): self
    {
        array_map(function ($param) {
            if (!($param instanceof ScopeParam)) {
                throw new MetadataException();
            }
        }, $params);

        $this->params = $params;

        return $this;
    }

    private function getParamsArray(): array
    {
        $paramsArray = [];
        foreach ($this->params as $param) {
            $paramsArray[] = $param->toArray();
        }

        return $paramsArray;
    }

    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'params' => $this->getParamsArray(),
        ];
    }

}
