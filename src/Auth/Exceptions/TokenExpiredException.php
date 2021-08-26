<?php

namespace Egal\Auth\Exceptions;

use Exception;

class TokenExpiredException extends Exception
{

    protected $message = 'Token expired!';
    protected $code = 401;

}
