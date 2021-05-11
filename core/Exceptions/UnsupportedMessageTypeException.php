<?php

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedMessageTypeException extends Exception
{

    protected $message = 'Неподдерживаемый тип сообщения!';
    protected $code = 500;

}
