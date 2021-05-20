<?php

namespace Egal\Core\Exceptions;

use Exception;

class NoAccessActionCallException extends Exception
{

    protected $message = 'No access to action call!';
    protected $code = 403;

}