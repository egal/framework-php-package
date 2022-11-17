<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Exceptions;

use Exception;

class ConfigMakeException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Config make exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
