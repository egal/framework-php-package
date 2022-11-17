<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UndefinedTypeOfMessageException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Undefined type of message!';

    /**
     * @var int
     */
    protected $code = 400;

}
