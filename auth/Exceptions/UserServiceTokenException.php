<?php

namespace Egal\Auth\Exceptions;

use Exception;

class UserServiceTokenException extends Exception
{

    protected $message = 'UserServiceTokenException';
    protected $code = 500;

}
