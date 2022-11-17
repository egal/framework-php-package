<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class TargetQueueNotProvidedException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Target queue not provided! For reply messages of action message must provide target queue!';

    /**
     * @var int
     */
    protected $code = 500;

}
