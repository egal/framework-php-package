<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class FilterException extends Exception
{

    protected $message = 'Filter Exception!';

    protected $code = 400;

}
