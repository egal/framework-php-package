<?php

namespace Egal\Validation\Rules;

use Illuminate\Contracts\Validation\Rule as IlluminateValidationRule;
use Illuminate\Support\Str;
use ReflectionClass;

abstract class Rule implements IlluminateValidationRule
{

    protected const VALIDATE_FUNCTION_NAME = 'validate';
    protected string $rule;

    /**
     * @param string $attribute
     * @param mixed $value
     * @return bool
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function passes($attribute, $value)
    {
        return $this->validate($attribute, $value);
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return 'Rule check failed '
            . (new ReflectionClass(static::class))->getShortName()
            . ' of :attribute attribute!';
    }

    abstract public function validate($attribute, $value, $parameters = null): bool;

    public function getCallback(): string
    {
        return static::class . '@' . static::VALIDATE_FUNCTION_NAME;
    }

    public function getRule(): string
    {
        return $this->rule ?? Str::snake(str_replace(
                'Rule',
                '',
                (new ReflectionClass(static::class))->getShortName()
            ));
    }

}
