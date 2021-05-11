<?php

namespace Egal\Core\Exceptions;

use Exception;

class EventHandlingException extends Exception
{

    protected $message = 'Unable to handle Event!';

}
