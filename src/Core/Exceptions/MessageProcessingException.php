<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class MessageProcessingException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Message processing exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
