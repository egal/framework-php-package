<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

/**
 * Class EgalCoreInitializationException
 */
class EgalCoreInitializationException extends Exception
{

    /**
     * The error message
     */
    protected $message = 'Egal Core initialization Exception';

    /**
     * The error code
     */
    protected $code = 500;

}
