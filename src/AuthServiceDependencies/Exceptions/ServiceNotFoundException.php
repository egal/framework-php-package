<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class ServiceNotFoundException extends Exception
{

    protected $code = 400;

    public static function make(string $service): self
    {
        $exception = new static();
        $exception->message = 'Service ' . $service . ' not found!';

        return $exception;
    }

}
