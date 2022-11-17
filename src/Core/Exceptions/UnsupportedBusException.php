<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class UnsupportedBusException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unsupported Bus exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
