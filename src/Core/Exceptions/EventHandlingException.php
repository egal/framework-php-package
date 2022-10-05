<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class EventHandlingException extends Exception
{

    protected $message = 'Unable to handle Event!';

    protected $code = 500;

}
