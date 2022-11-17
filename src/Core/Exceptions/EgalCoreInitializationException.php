<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class EgalCoreInitializationException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Egal Core initialization exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
