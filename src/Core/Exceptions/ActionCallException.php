<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ActionCallException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Action call error!';

    /**
     * @var int
     */
    protected $code = 400;

}
