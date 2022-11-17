<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class ModelMetadataTagContainsSpaceException extends Exception
{

    /**
     * @var int
     */
    protected $code = 500;

    public static function make(string $tag): self
    {
        $exception = new static();
        $exception->message = "Metadata $tag tag\'s description must not contain spaces!";

        return $exception;
    }

}
