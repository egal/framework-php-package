<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class IncorrectCaseOfPropertyVariableNameException extends Exception
{

    /**
     * @var int
     */
    protected $code = 500;

    public static function make(string $modelClass, string $propertyVariableName): self
    {
        $exception = new static();
        $exception->message = 'Property variable name must be in snake case!';

        if (config('app.debug')) {
            $exception->message .= ' [' . $modelClass . '::$' . $propertyVariableName . ']';
        }

        return $exception;
    }

}
