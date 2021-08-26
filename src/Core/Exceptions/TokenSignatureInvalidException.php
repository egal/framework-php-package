<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class TokenSignatureInvalidException extends Exception
{

    protected $message = 'Invalid token signature!';

    protected $code = 401;

}
