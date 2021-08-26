<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class EventProcessingException extends Exception
{

    protected $message = 'Event processing exception!';

}
