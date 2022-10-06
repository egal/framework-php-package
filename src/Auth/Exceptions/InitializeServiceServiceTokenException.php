<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeServiceServiceTokenException extends Exception
{

    protected $message = 'Initialize service service token exception!';

    protected $code = 400;

}
