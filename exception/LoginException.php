<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class LoginException extends Exception
{

    protected $message = 'Вход невозможен!';
    protected $code = 403;

}
