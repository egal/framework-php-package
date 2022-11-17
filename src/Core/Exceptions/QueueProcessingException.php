<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class QueueProcessingException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Queue processing exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
