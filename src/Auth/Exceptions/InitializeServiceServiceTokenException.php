<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeServiceServiceTokenException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Initialize service service token exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
