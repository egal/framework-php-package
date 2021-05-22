<?php

namespace Egal\Core\Exceptions;

use Exception;

class UndefinedTypeOfMessageException extends Exception
{

    protected $message = 'UndefinedTypeOfMessageException';
    protected $code = 400;

}
