<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class InitializeMessageFromArrayException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Initialize message from array exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
