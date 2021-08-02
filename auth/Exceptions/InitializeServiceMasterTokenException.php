<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeServiceMasterTokenException extends Exception
{

    protected $message = 'Initialize service master token exception!';

    protected $code = 400;

}
