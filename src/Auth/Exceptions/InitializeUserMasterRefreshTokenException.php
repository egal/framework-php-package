<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeUserMasterRefreshTokenException extends Exception
{

    protected $message = 'Initialize user master refresh token exception!';

    protected $code = 400;

}
