<?php

namespace Egal\Core\Exceptions;

use Exception;

class ObjectNotFoundException extends Exception
{

    protected $message = 'Not found!';
    protected $code = 404;

}
