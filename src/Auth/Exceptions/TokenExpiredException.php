<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class TokenExpiredException extends Exception
{

    protected $code = 401;

    public static function make(string $tokenType): self
    {
        $result = new static();
        $result->message = 'Token ' . $tokenType . ' expired!';

        return $result;
    }

}
