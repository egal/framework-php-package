<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ResponseException extends Exception
{

    protected $message = 'Response exception!';

    protected $code = 500;

}
