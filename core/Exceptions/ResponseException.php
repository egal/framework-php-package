<?php

namespace Egal\Core\Exceptions;

use Exception;

class ResponseException extends Exception
{

    protected $message = 'Response Exception!';
    protected $code = 500;

}
