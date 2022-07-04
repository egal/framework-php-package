<?php

namespace Egal\Interface\Metadata\Components\Table\Computed;

class PostfixComputed extends Computed
{
    public static function isValidValue(mixed $value): bool
    {
        return is_string($value);
    }
}
