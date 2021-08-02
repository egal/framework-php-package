<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeUserServiceTokenException extends Exception
{

    protected $message = 'Initialize user service token exception!';

    protected $code = 400;

}
