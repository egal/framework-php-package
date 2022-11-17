<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class NotFoundException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Not found!';

    /**
     * @var int
     */
    protected $code = 500;

}
