<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Egal\Exception\InternalException;

class TokenExpiredException extends InternalException
{

    protected $code = 401;

    protected string $internalCode = 'Token expired';

    public static function make(string $tokenType): self
    {
        $result = new static();
        $result->message = 'Token ' . $tokenType . ' expired!';
        $result->internalCode = $tokenType . ' expired';

        return $result;
    }

}
