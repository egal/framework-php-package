<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFieldPatternInFilterConditionException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Unsupported filter condition field form!';

    /**
     * @var int
     */
    protected $code = 403;

}
