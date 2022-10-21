<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class AuthenticationFailedException extends Exception
{

    protected $message = 'Authentication failed!';

    protected $code = 401;

}
