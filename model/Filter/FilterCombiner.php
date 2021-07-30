<?php

declare(strict_types=1);

namespace Egal\Model\Filter;

use Egal\Model\Exceptions\FilterException;

class FilterCombiner
{

    public const AND = 'AND';
    public const OR = 'OR';

    private string $value;

    public function __construct(string $value)
    {
        $this->setValue($value);
    }

    /**
     * @throws \Egal\Model\Exceptions\FilterException
     */
    public static function fromString(string $string): FilterCombiner
    {
        if (!static::mayMakeFromString($string)) {
            throw new FilterException('Invalid condition combiner format!');
        }

        return new FilterCombiner($string);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public static function mayMakeFromString(string $string): bool
    {
        return is_string($string)
            && in_array(strtoupper($string), [self::AND, self::OR]);
    }

}
