<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ActionNotFoundException extends Exception
{

    protected $code = 500;

    public static function make(string $modelName, string $actionName): self
    {
        $exception = new static();
        $exception->message = $actionName . ' not found in ' . $modelName . '!';

        return $exception;
    }

}
