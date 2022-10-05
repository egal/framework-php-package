<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterConditionException extends Exception
{

    protected $message = 'Unsupported filter condition!';

    protected $code = 403;

}
