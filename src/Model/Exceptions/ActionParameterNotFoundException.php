<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ActionParameterNotFoundException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Action parameter not found!';

    /**
     * @var int
     */
    protected $code = 403;

    public static function make(string $parameter): self
    {
        $exception = new static();
        $exception->message = 'Action parameter ' . $parameter . ' not found!';

        return $exception;
    }

}
