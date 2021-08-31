<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class InitializeFilterPartException extends Exception
{

    protected $message = 'Failed initialize filter part!';

    protected $code = 403;

}
