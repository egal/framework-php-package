<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

/**
 * Class UnableDetermineMessageTypeException
 */
class UnableDetermineMessageTypeException extends Exception
{

    /**
     * The error message
     */
    protected $message = 'Unable determine message type!';

    /**
     * The error code
     */
    protected $code = 500;

}
