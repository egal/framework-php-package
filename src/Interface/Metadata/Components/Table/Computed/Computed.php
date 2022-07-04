<?php

namespace Egal\Interface\Metadata\Components\Table\Computed;

use Egal\Interface\Metadata\Configuration;

abstract class Computed extends Configuration implements ComputedInterface
{
    private string $name;

    private mixed $value;

    public static function make(mixed $value): self
    {
        $computed = new static();

        if (!static::isValidValue($value)) {
            // TODO Exception
        }

        $computed->value = $value;
        $computed->name = str_replace(self::class, '', static::class);

        return $computed;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }
}
