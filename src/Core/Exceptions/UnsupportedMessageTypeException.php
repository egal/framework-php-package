<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedMessageTypeException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unsupported message type!';

    /**
     * @var int
     */
    protected $code = 500;

}
