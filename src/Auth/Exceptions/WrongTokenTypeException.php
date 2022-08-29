<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class WrongTokenTypeException extends Exception
{
    
    protected $message = 'Wrong token type!';
    
    protected $code = 401;

}
