<?php

namespace Egal\Core\Exceptions;

interface HasDomainCode
{
    public function setDomainCode($code): void;

    public function getDomainCode(): string;
}
