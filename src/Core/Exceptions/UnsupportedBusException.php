<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedBusException extends Exception
{

    protected $message = 'Unsupported Bus exception!';

    protected $code = 500;

}
