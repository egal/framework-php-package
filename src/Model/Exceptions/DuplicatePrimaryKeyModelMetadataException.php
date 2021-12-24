<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class DuplicatePrimaryKeyModelMetadataException extends Exception
{

    protected $code = 500;

    public static function make(string $modelName): self
    {
        $exception = new static();
        $exception->message = 'Duplicate primary key in model' . $modelName . ' metadata!';

        return $exception;
    }

}
