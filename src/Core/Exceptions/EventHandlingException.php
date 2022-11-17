<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class EventHandlingException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unable to handle Event!';

    /**
     * @var int
     */
    protected $code = 500;

}
