<?php

namespace Egal\Exception;

class ActionCallException extends Exception
{

    protected const MESSAGE_PREFIX_LINE = 'Ошибка вызова функции!';
    protected $code = 403;

}
