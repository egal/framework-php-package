<?php

declare(strict_types=1);

namespace Egal\Model\Filter;

use Egal\Model\Exceptions\FilterException;

class FilterCondition
{

    private string $field;

    private string $operator;

    /**
     * @var mixed
     */
    private $value = null;

    /**
     * @param mixed[] $array
     * @throws \Egal\Model\Exceptions\FilterException
     */
    public static function fromArray(array $array): FilterCondition
    {
        if (!self::mayMakeFromArray($array)) {
            throw new FilterException('Incorrect filter structure!');
        }

        $filterCondition = new FilterCondition();
        $filterCondition->setField($array[0]);
        $filterCondition->setOperator($array[1]);
        $filterCondition->setValue($array[2]);

        return $filterCondition;
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public static function mayMakeFromArray($value): bool
    {
        return is_array($value)
            && count($value) === 3
            && is_string($value[0])
            && is_string($value[1]);
    }

    /**
     * @param mixed $value
     */
    private function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @throws \Egal\Model\Exceptions\FilterException
     */
    private function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    private function setField(string $field): void
    {
        $this->field = $field;
    }

}
