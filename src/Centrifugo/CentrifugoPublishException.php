<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Exception;

class CentrifugoPublishException extends Exception
{

    /**
     * @var int
     */
    protected $code = 500;

    public static function make(string $error): self
    {
        $exception = new static();
        $exception->message = 'Centrifuge publish throws with exception!';

        if (config('app.debug')) {
            $exception->message .= PHP_EOL . $error;
        }

        return $exception;
    }

}
