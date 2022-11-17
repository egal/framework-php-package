<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeUserMasterTokenException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Initialize user master token exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
