<?php

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class PasswordHashException extends Exception
{

    protected $message = 'Password hash error!';

    protected $code = 500;

}
