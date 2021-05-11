<?php

namespace Egal\Core\Exceptions;

use Exception;

class ActionMessageException extends Exception
{

    protected $message = 'Action Message Exception';
    protected $code = 500;

}
