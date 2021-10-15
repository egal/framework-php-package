<?php

namespace Egal\Core\Exceptions;

use Exception;
use Throwable;

abstract class DomainClassifiedException extends Exception implements HasDomainCode
{
    static private $allDomainCodes = [];
    private string $domainCode;

    public function __construct($domainCode, $message = "", $code = 0, Throwable $previous = null)
    {
        if (in_array($domainCode, self::$allDomainCodes)) {
            throw NonUniqueDomainCodeException::make($domainCode);
        }
        $this->setDomainCode($domainCode);

        parent::__construct($message, $code, $previous);

        self::$allDomainCodes[] = $domainCode;
    }

    public function setDomainCode($code): void
    {
        $this->domainCode = $code;
    }

    public function getDomainCode(): string
    {
        return $this->domainCode;
    }
}
