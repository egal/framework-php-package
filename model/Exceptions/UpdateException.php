<?php

namespace Egal\Model\Exceptions;

use Exception;

class UpdateException extends Exception
{

    protected $message = 'Ошибка обновления сущности!';
    protected $code = 500;

}
