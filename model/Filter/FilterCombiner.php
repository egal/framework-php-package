<?php

namespace Egal\Model\Filter;

use Egal\Model\Exceptions\FilterException;

/**
 * @package Egal\Model
 */
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
     * @param $string
     * @return bool
     */
    public static function mayMakeFromString($string): bool
    {
        return is_string($string)
            && in_array(strtoupper($string), [FilterCombiner::AND, FilterCombiner::OR]);
    }

    /**
     * @param string $string
     * @return FilterCombiner
     * @throws FilterException
     */
    public static function fromString(string $string): FilterCombiner
    {
        if (!FilterCombiner::mayMakeFromString($string)) {
            throw new FilterException('Неверный формат объединителя условий!');
        }

        return new FilterCombiner($string);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

}
