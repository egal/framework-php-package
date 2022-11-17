<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UpdateManyException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Multiple entity update error!';

    /**
     * @var int
     */
    protected $code = 500;

}
