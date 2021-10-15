<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class NoInternalCodeSetException extends Exception
{

    protected $message = 'No internal code set for InternalException!';

    protected $code = 500;

}
