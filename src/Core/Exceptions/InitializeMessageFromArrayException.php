<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class InitializeMessageFromArrayException extends Exception
{

    protected $message = 'Impossible to create InternalException without internal code!';

    protected $code = 500;

}
