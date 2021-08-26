<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UndefinedTypeOfMessageException extends Exception
{

    protected $message = 'Undefined type of message!';

    protected $code = 400;

}
