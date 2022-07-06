<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class ServiceLoginException extends Exception
{

    protected $code = 500;

}
