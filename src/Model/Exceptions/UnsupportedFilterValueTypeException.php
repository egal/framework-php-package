<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterValueTypeException extends Exception
{

    protected $code = 403;

    public static function make(string $field, string $requiredType): self
    {
        $exception = new static();
        $exception->message = 'Unsupported filter value type for field - ' . $field . '!'
            . PHP_EOL . 'Required type - ' . $requiredType;

        return $exception;
    }

}
