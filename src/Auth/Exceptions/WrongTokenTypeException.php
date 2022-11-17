<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class WrongTokenTypeException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Wrong token type!';

    /**
     * @var int
     */
    protected $code = 401;

}
