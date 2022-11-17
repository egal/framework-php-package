<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UpdateException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Entity update exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
