<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeUserMasterTokenException extends Exception
{

    protected $message = 'Initialize user master token exception!';

    protected $code = 400;

}
