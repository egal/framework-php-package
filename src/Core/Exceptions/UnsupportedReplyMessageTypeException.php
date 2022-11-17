<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedReplyMessageTypeException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unsupported reply message type!';

    /**
     * @var int
     */
    protected $code = 500;

}
