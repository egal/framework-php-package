<?php

namespace Egal\Core\Rest\Filter;

abstract class AbstractField
{
    abstract public static function fromString(string $fieldString): AbstractField;
}
