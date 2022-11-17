<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class LoginException extends Exception
{

    /**
     * @var int
     */
    protected $code = 400;

}
