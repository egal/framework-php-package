<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeUserServiceTokenException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Initialize user service token exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
