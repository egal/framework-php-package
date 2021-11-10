<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Exception;

class CentrifugoInitException extends Exception
{

    protected $code = 500;

    public static function make(string ...$requiredParams): self
    {
        $exception = new static();
        $exception->message = 'Centrifuge initialization throws with exception!';

        if (config('app.debug')) {
            $exception->message .= PHP_EOL . 'Required params:'
                . PHP_EOL . implode(', ', $requiredParams) . '.';
        }

        return $exception;
    }

}
