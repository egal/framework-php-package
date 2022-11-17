<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class AuthenticationFailedException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Authentication failed!';

    /**
     * @var int
     */
    protected $code = 401;

}
