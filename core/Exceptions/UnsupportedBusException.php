<?php

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedBusException extends Exception
{
    protected $message = 'UnsupportedBusException';
    protected $code = 500;
}
