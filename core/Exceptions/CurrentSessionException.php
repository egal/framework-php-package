<?php

namespace Egal\Core\Exceptions;

use Exception;

class CurrentSessionException extends Exception
{

    protected $message = 'Current Session Exception!';
    protected $code = 500;

}
