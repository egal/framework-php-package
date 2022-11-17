<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class InitializeServiceMasterTokenException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Initialize service master token exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
