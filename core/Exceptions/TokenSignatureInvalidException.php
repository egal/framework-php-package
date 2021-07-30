<?php

namespace Egal\Core\Exceptions;

use Exception;

class TokenSignatureInvalidException extends Exception
{

    protected $message = 'Invalid token signature!';

    protected $code = 401;

}
