<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;
use Throwable;

abstract class InternalException extends Exception implements HasInternalCode
{

    protected string $internalCode;

    public function __construct($message = '', $code = 0, $internalCode = null, ?Throwable $previous = null)
    {
        if (isset($internalCode)) {
            $this->internalCode = $internalCode;
        }

        parent::__construct($message, $code, $previous);

        if (!isset($this->internalCode)) {
            throw new NoInternalCodeSetException();
        }
    }

    public function getInternalCode(): string
    {
        return $this->internalCode;
    }

}
