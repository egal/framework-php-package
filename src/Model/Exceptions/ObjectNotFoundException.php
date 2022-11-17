<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ObjectNotFoundException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Object not found!';

    /**
     * @var int
     */
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
