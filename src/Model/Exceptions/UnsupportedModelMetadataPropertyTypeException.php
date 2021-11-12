<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedModelMetadataPropertyTypeException extends Exception
{

    protected $code = 500;

    public static function make(string $type): self
    {
        $exception = new static();
        $exception->message = 'Metadata property-type ' . $type . ' unsupported!';

        return $exception;
    }

}
