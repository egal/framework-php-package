<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class UserNotIdentifiedException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'User not identified!';

    /**
     * @var int
     */
    protected $code = 400;

}
