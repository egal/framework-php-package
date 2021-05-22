<?php

namespace Egal\Core\Exceptions;

use Exception;

class ModelNotFoundException extends Exception
{

    protected $message = 'ModelNotFoundException';
    protected $code = 404;

}
