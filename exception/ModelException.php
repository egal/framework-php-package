<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class ModelException extends Exception
{

    protected $message = 'Ошибка модели!';
    protected $code = 500;

}
