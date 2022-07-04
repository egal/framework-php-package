<?php

namespace Egal\Core\Exceptions;

use Exception;

class NoAccessException extends Exception
{

    protected $message = 'No access!';
    protected $code = 403;

}
