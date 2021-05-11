<?php

namespace EgalFramework\Common\Interfaces;

use Throwable;

interface ExceptionInterface
{

    public function __construct($message = '', $code = 0, Throwable $previous = null);

}
