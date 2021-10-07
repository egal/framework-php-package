<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class LoginException extends Exception
{

    protected $code = 400;

}
