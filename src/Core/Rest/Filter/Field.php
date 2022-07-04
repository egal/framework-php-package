<?php

namespace Egal\Core\Rest\Filter;

class Field extends AbstractField
{
    public const REG_PATTERN = '[a-z_]+';

    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromString(string $fieldString): Field
    {
        return new self($fieldString);
    }

}
