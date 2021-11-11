<?php

namespace Egal\Core\Exceptions;

use Exception;

class TargetQueueNotProvidedException extends Exception
{

    protected $code = 500;

    protected $message = 'Target queue not provided! For reply messages of action message must provide target queue!';

}
