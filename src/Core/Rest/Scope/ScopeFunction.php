<?php

namespace Egal\Core\Rest\Scope;


class ScopeFunction
{
    public const REG_PATTERN = "/(?<scope>[a-zA-Z]+)\((?<parameters>[^)]*)\)/m";
    public const PARAMETER_REG_PATTERN = "/(?<key>\w+)\s*=\s*(?<value>.+)/";
    public const PARAMETERS_DELIMITER = ",";
    public const SCOPES_DELIMITER = ",";

    protected string $name;
    protected array $parameters;

    public static function make(string $name, array $parameters = [])
    {
        $scopeFunction = new static();
        $scopeFunction->name = $name;
        $scopeFunction->parameters = $parameters;

        return $scopeFunction;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
