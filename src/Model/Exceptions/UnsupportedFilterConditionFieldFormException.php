<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterConditionFieldFormException extends Exception
{

    protected $message = 'Unsupported filter condition field form!';

    protected $code = 403;

}
