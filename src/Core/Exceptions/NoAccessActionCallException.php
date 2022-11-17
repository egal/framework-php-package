<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class NoAccessActionCallException extends Exception
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
