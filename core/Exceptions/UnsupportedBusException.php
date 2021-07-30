<?php

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedBusException extends Exception
{
    protected $message = 'Unsupported Bus Exception';

    protected $code = 500;
}
