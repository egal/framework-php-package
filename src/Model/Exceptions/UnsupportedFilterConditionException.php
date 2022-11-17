<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterConditionException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unsupported filter condition!';

    /**
     * @var int
     */
    protected $code = 403;

}
