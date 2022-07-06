<?php

declare(strict_types=1);

namespace Egal\Auth\Exceptions;

use Exception;

class TokenTypeNotSpecifiedException extends Exception
{

    protected $message = 'Token type not specified!';

    protected $code = 400;

}
