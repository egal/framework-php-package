<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class CurrentSessionException extends Exception
{

    protected $message = 'Current session exception!';

    protected $code = 500;

}
