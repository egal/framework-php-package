<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class UndefinedTokenTypeException extends Exception
{
    
    protected $message = 'Undefined token type!';

    protected $code = 401;

}
