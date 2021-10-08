<?php

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterFieldException extends Exception
{

    protected $message = 'Unsupported filter field!';

    protected $code = 403;

}
