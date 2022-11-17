<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class RequestException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Request exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
