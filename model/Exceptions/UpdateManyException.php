<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UpdateManyException extends Exception
{

    protected $message = 'Multiple entity update error!';

    protected $code = 500;

}
