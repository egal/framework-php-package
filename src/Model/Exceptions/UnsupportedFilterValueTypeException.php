<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterValueTypeException extends Exception
{

    protected $code = 403;

    public static function make($field, $errors): self
    {
        $exception = new static();
        $exception->message = 'Unsupported filter value type for field - ' . $field . '!';

        if (config('app.debug')) {
            foreach ($errors as $field => $error) {
                $exception->message .= PHP_EOL . $error;
            }
        }

        return $exception;
    }

}
