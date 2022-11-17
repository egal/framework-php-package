<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class DeleteManyException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Multiple entity deletion error!';

    /**
     * @var int
     */
    protected $code = 400;

}
