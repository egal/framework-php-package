<?php

namespace Egal\Model\Filter;

use Egal\Model\Exceptions\FilterException;

/**
 * @package Egal\Model
 */
class FilterCondition
{

    public const EQUAL_OPERATOR = 'eq';
    public const NOT_EQUAL_OPERATOR = 'ne';
    public const GREATER_THEN_OPERATOR = 'gt';
    public const LESS_THEN_OPERATOR = 'lt';
    public const GREATER_OR_EQUAL_OPERATOR = 'ge';
    public const LESS_OR_EQUAL_OPERATOR = 'le';
    public const CONTAIN_OPERATOR = 'co';
    public const START_WITH_OPERATOR = 'sw';
    public const END_WITH_OPERATOR = 'ew';
    public const NOT_CONTAIN_OPERATOR = 'nc';

    private string $field;
    private ?string $relationName = null;
    private string $operator;
    private string $originOperator;
    private $value = null;

    /**
     * FilterCondition constructor.
     * @param string $field
     * @param string $operator
     * @param mixed|null $value
     * @throws FilterException
     */
    public function __construct(string $field, string $operator, $value = null)
    {
        $this->setField($field);
        $this->setOperator($operator);
        $this->setValue($value);
    }

    /**
     * @param array $array
     * @return FilterCondition
     * @throws FilterException
     */
    public static function fromArray(array $array): FilterCondition
    {
        if (!self::mayMakeFromArray($array)) {
            throw new FilterException('Incorrect filter structure!');
        }

        return new FilterCondition($array[0], $array[1], $array[2]);
    }

    /**
     * @param $array
     * @return bool
     */
    public static function mayMakeFromArray($array): bool
    {
        return is_array($array) && self::isCorrectFormat($array);
    }

    /**
     * Check array has correct params format
     *
     * @param array $array
     * @return bool
     */
    public static function isCorrectFormat(array $array): bool
    {
        return count($array) === 3
            && is_string($array[0])
            && is_string($array[1]);
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @param string $field
     * @throws FilterException
     */
    public function setField(string $field): void
    {
        if (str_contains($field, '.')) {
            $parts = explode('.', $field);
            if (count($parts) < 2) {
                throw new FilterException('Field format is not correct!');
            }
            $this->relationName = $parts[count($parts) - 2];
            $this->field = $parts[count($parts) - 1];
        } else {
            $this->field = $field;
        }
    }

    /**
     * @return string|null
     */
    public function getRelationName(): ?string
    {
        return $this->relationName;
    }

    /**
     * @noinspection PhpUnused
     */
    public function setRelationName($relationName)
    {
        $this->relationName = $relationName;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     * @throws FilterException
     */
    public function setOperator(string $operator): void
    {
        $this->originOperator = $operator;
        $this->operator = $this->getSqlOperator();
    }

    /**
     * @return string
     * @throws FilterException
     */
    protected function getSqlOperator(): string
    {
        switch ($this->originOperator) {
            case self::EQUAL_OPERATOR:
                return '=';
            case self::NOT_EQUAL_OPERATOR:
                return '!=';
            case self::GREATER_THEN_OPERATOR:
                return '>';
            case self::LESS_THEN_OPERATOR:
                return '<';
            case self::GREATER_OR_EQUAL_OPERATOR:
                return '>=';
            case self::LESS_OR_EQUAL_OPERATOR:
                return '<=';
            case self::CONTAIN_OPERATOR:
            case self::START_WITH_OPERATOR:
            case self::END_WITH_OPERATOR:
                return 'LIKE';
            case self::NOT_CONTAIN_OPERATOR:
                return 'NOT LIKE';
        }
        throw new FilterException('Incorrect operator!');
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
    public function setValue($value): void
    {
        $this->value = $this->prepareValue($value);
    }

    /**
     * @param $value
     * @return string|int|null
     */
    protected function prepareValue($value)
    {
        switch ($this->originOperator) {
            case self::CONTAIN_OPERATOR:
            case self::NOT_CONTAIN_OPERATOR:
                return '%' . $value . '%';
            case self::START_WITH_OPERATOR:
                return $value . '%';
            case self::END_WITH_OPERATOR:
                return '%' . $value;
            default:
                return $value;
        }
    }

}
