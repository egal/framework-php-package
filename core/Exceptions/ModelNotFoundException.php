<?php

declare(strict_types=1);

namespace Egal\Core\Exceptions;

use Exception;

class ModelNotFoundException extends Exception
{

    protected $message = 'Model not found!';

    protected $code = 404;

}
