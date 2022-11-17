<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeUserMasterRefreshTokenException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Initialize user master refresh token exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
