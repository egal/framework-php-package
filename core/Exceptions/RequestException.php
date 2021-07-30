<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class RequestException extends Exception
{

    protected $message = 'Request Exception!';

    protected $code = 500;

}
