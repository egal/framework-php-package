<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

abstract class InternalException extends Exception implements HasInternalCode
{

    protected string $internalCode = '';

    public function getInternalCode(): string
    {
        return $this->internalCode;
    }

}
