<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class InitializeFilterPartException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Failed initialize filter part!';

    /**
     * @var int
     */
    protected $code = 403;

}
