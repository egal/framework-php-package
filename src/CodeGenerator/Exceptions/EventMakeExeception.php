<?php

namespace Egal\CodeGenerator\Exceptions;

class EventMakeExeception
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
