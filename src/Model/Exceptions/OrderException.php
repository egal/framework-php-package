<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class OrderException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Sort error!';

    /**
     * @var int
     */
    protected $code = 500;

}
