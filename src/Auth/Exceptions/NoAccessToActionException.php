<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class NoAccessToActionException extends Exception
{

    protected $message = 'No access to action call!';

    protected $code = 403;

}
