<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class CurrentSessionException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Current session exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
