<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class BusCreatorException extends Exception
{

    /**
     * @var string
     */
    protected $message = 'Bus Creator exception!';

    /**
     * @var int
     */
    protected $code = 500;

}
