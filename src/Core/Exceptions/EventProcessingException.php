<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class EventProcessingException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Event processing exception!';

}
