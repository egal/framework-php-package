<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class UndefinedTokenTypeException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Undefined token type!';

    /**
     * @var int
     */
    protected $code = 401;

}
