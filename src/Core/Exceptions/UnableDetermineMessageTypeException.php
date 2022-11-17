<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnableDetermineMessageTypeException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unable determine message type!';

    /**
     * @var int
     */
    protected $code = 500;

}
