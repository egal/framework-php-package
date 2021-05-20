<?php

namespace Egal\Model\Exceptions;

use Exception;

class DeleteManyException extends Exception
{

    protected $message = 'Multiple entity deletion error!';
    protected $code = 400;

}
