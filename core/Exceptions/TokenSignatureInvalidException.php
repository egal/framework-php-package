<?php

namespace Egal\Core\Exceptions;

use Exception;

class TokenSignatureInvalidException extends Exception
{

    protected $message = 'Token signature invalid!';
    protected $code = 401;

}
