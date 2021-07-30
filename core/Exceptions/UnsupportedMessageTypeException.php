<?php

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedMessageTypeException extends Exception
{

    protected $message = 'Unsupported message type!';

    protected $code = 500;

}
