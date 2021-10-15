<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

interface HasInternalCode
{

    public function getInternalCode(): string;

}
