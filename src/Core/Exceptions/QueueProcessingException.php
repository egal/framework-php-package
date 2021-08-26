<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class QueueProcessingException extends Exception
{

    protected $message = 'Queue processing exception!';

    protected $code = 500;

}
