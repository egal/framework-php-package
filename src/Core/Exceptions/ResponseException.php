<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ResponseException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Response exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
