<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ModelNotFoundException extends Exception
{

    /**
     * @var int
     */
    protected $code = 404;

    public static function make(string $modelClassName): self
    {
        $exception = new static();
        $exception->message = 'Model ' . $modelClassName . ' not found!';

        return $exception;
    }

}
