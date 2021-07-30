<?php

namespace Egal\Core\Exceptions;

use Exception;

class QueueProcessingException extends Exception
{

    protected $message = 'Queue processing Exception';

    protected $code = 500;

}
