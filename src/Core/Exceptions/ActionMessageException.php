<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ActionMessageException extends Exception
{

    protected $message = 'Action message exception!';

    protected $code = 500;

}
