<?php

namespace Egal\Core\Exceptions;

use Exception;

class NonUniqueDomainCodeException extends Exception
{
    protected $code = 500;

    public static function make(string $domainCode): self
    {
        $exception = new static();
        $exception->message = 'Exception with domain code' . $domainCode . ' already constructed!';

        return $exception;
    }

}
