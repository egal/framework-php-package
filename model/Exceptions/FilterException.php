<?php

namespace Egal\Model\Exceptions;

use Exception;

class FilterException extends Exception
{

    protected $message = 'Ошибка фильтрации!';
    protected $code = 400;

}
