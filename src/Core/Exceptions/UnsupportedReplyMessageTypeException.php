<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedReplyMessageTypeException extends Exception
{

    protected $message = 'Unsupported reply message type!';

    protected $code = 500;

}
