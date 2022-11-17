<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ActionMessageException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Action message exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
