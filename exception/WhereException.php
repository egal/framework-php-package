<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class WhereException extends Exception
{

    protected $message = 'Ошибка поиска!';
    protected $code = 405;

}
