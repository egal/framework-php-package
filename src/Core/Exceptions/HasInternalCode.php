<?php

namespace Egal\Core\Exceptions;

interface HasInternalCode
{
    public function getInternalCode(): string;
}
