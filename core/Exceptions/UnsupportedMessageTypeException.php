<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedMessageTypeException extends Exception
{

    protected $message = 'Unsupported message type!';

    protected $code = 500;

}
