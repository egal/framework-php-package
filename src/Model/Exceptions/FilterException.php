<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class FilterException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Filter exception!';

    /**
     * @var int
     */
    protected $code = 400;

}
