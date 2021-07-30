<?php

namespace Egal\Core\Exceptions;

use Exception;

class UndefinedTypeOfMessageException extends Exception
{

    protected $message = 'Undefined type of message!';

    protected $code = 400;

}
