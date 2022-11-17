<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class HashGuardException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Hash guard exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
