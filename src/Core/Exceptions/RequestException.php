<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class RequestException extends Exception
{

    protected $message = 'Request exception!';

    protected $code = 500;

}
