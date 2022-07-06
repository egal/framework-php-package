<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class IncorrectTokenTypeException extends Exception
{

    protected $message = 'Incorrect token type specified!';

    protected $code = 400;

}
