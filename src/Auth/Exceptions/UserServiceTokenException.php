<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class UserServiceTokenException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'User service token exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
