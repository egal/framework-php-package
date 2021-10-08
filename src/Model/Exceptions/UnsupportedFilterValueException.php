<?php

namespace Egal\Model\Exceptions;

use Exception;

class UnsupportedFilterValueException extends Exception
{

    protected $message = 'Unsupported filter value!';

    protected $code = 403;

}
