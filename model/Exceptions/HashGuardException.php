<?php

namespace Egal\Model\Exceptions;

use Exception;

class HashGuardException extends Exception
{

    protected $message = 'Ошибка защиты целостности данных!';

}
