<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class NoAccessToActionException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'No access to action call!';

    /**
     * @var int
     */
    protected $code = 403;

}
