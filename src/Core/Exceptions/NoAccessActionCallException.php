<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class NoAccessActionCallException extends Exception
{

    protected $message = 'No access to action call!';

    protected $code = 403;

}
