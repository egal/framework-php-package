<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ObjectNotFoundException extends Exception
{

    protected $message = 'Object not found!';

    protected $code = 404;

    public static function make($index): self
    {
        $exception = new static();

        if (config('app.debug')) {
            $exception->message = 'Object not found with ' . $index . ' index!';
        }

        return $exception;
    }

}
