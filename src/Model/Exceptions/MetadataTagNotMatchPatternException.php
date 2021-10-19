<?php

namespace Egal\Model\Exceptions;

use Exception;

class MetadataTagNotMatchPatternException extends Exception
{

    protected $code = 500;

    public static function make(string $tag, string $pattern): self
    {
        $exception = new static();
        $exception->message = 'Metadata tag not match pattern!';

        if (config('app.debug')) {
            $exception->message .= PHP_EOL . 'Tag ' . $tag . 'should match pattern ' . $pattern;
        }

        return $exception;
    }

}
