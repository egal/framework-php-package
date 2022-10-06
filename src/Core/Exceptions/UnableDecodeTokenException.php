<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnableDecodeTokenException extends Exception
{

    protected $message = 'Unable to decode token!';

    protected $code = 401;

}
