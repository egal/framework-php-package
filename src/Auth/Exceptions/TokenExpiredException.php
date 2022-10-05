<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Egal\Exception\InternalException;
use Illuminate\Support\Str;

class TokenExpiredException extends InternalException
{

    protected $code = 401;

    protected string $internalCode = 'token_expired';

    public static function make(string $tokenType): self
    {
        $result = new static();
        $result->message = 'Token ' . Str::upper($tokenType) . ' expired!';
        $result->internalCode = $tokenType . '_expired';

        return $result;
    }

}
