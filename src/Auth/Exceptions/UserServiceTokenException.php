<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class UserServiceTokenException extends Exception
{

    protected $message = 'User service token exception!';

    protected $code = 500;

}
