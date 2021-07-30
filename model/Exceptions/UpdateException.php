<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class UpdateException extends Exception
{

    protected $message = 'Entity update Exception!';

    protected $code = 500;

}
