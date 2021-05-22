<?php

namespace Egal\Model\Exceptions;

use Exception;

class UpdateManyException extends Exception
{

    protected $message = 'Ошибка множественного обновления сущностей!';
    protected $code = 500;

}
