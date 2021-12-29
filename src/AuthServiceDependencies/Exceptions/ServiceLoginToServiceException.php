<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class ServiceLoginToServiceException extends Exception
{

    protected $code = 500;

}
