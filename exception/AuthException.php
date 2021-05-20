<?php

namespace Egal\Exception;

use Exception;

/**
 * Class AuthException
 * @package Egal\Exception
 * @deprecated
 */
class AuthException extends Exception
{

    protected $message = 'Authorisation error!';
    protected $code = 401;

}
