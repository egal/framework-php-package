<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Exceptions;

use Exception;

class EventMakeException extends Exception
{

    protected $code = 500;

    public static function make(string $error): self
    {
        $exception = new static();
        $exception->message = 'Event make exception!';

        if (config('app.debug')) {
            $exception->message .= PHP_EOL . $error;
        }

        return $exception;
    }

}
