<?php

namespace Egal\Core\Exceptions;

use Exception;

class RequestException extends Exception
{

    protected $message = 'RequestException';
    protected $code = 500;

}
