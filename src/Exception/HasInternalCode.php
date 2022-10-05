<?php

declare(strict_types=1);

namespace Egal\Exception;

interface HasInternalCode
{

    public function getInternalCode(): string;

}
