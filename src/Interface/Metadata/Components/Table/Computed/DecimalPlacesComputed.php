<?php

namespace Egal\Interface\Metadata\Components\Table\Computed;

class DecimalPlacesComputed extends Computed
{
    public static function isValidValue(mixed $value): bool
    {
        return is_int($value);
    }
}
