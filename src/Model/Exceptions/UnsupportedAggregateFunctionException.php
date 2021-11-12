<?php

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedAggregateFunctionException extends Exception
{
    protected $message = 'Unsupported aggregate function!';

    protected $code = 403;

    public static function make(string $function): self
    {
        $exception = new static();
        $exception->message = "Unsupported $function aggregate function!";

        return $exception;
    }

}
