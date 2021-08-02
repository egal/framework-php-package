<?php

declare(strict_types=1);

namespace Egal\CodeGenerator\Exceptions;

use Exception;

class ConfigMakeException extends Exception
{

    protected $message = 'Config make exception!';

    protected $code = 500;

}
