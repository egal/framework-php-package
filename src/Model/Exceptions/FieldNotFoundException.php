<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class FieldNotFoundException extends Exception
{

    protected $message = 'Field not found!';

    protected $code = 403;

    public static function make(string $field): self
    {
        $exception = new static();
        $exception->message = 'Field ' . $field . ' not found!';

        return $exception;
    }

}
