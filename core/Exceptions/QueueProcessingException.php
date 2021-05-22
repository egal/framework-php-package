<?php

namespace Egal\Core\Exceptions;

use Exception;

class QueueProcessingException extends Exception
{

    protected $message = 'QueueProcessingException';
    protected $code = 500;

}
