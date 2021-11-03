<?php

declare(strict_types=1);

namespace Egal\Exception;

use Exception;
use Throwable;

abstract class InternalException extends Exception implements HasInternalCode
{

    protected string $internalCode;

    public function __construct($message = '', $code = 0, $internalCode = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if (isset($internalCode)) {
            $this->internalCode = $internalCode;
        } elseif (!isset($this->internalCode)) {
            throw new NoInternalCodeSetException();
        }
    }

    public function getInternalCode(): string
    {
        return $this->internalCode;
    }

}
