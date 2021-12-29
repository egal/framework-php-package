<?php

namespace Egal\Auth\Exceptions;

use Exception;

class IncorrectTokenTypeException extends Exception
{
    protected $message = 'Incorrect token type specified!';

    protected $code = 400;

}
