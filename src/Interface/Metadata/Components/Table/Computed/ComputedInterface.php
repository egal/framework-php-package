<?php

namespace Egal\Interface\Metadata\Components\Table\Computed;

interface ComputedInterface
{
    public static function make(mixed $value): self;

    public function getName(): string;

    public function getValue();

    public static function isValidValue(mixed $value): bool;
}
