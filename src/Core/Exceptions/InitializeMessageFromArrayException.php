<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class InitializeMessageFromArrayException extends Exception
{

    protected $message = 'Initialize message from array exception!';

    protected $code = 400;

}
