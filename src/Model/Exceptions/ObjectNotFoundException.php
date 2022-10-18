<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ObjectNotFoundException extends Exception
{

    protected $message = 'Object not found!';

    protected $code = 404;

    /**
     * @return static
     */
    public static function make(mixed $key): self
    {
        $exception = new static();

        if (config('app.debug')) {
            $exception->message = 'Object not found with ' . $key . ' identifier!';
        }

        return $exception;
    }

}
