<?php

namespace Egal\Auth\Exceptions;

use Exception;

class TokenTypeNotSpecifiedException extends Exception
{

    protected $message = 'Token type not specified!';

    protected $code = 400;

}
