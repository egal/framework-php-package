<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class OrderException extends Exception
{

    protected $message = 'Sort error!';

    protected $code = 500;

}
