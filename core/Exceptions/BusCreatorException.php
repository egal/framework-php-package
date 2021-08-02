<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class BusCreatorException extends Exception
{

    protected $message = 'Bus Creator exception!';

    protected $code = 500;

}
