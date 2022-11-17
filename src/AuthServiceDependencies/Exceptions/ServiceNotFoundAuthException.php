<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Exceptions;

use Exception;

class ServiceNotFoundAuthException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Service not found!';

    /**
     * @var int
     */
    protected $code = 400;

}
