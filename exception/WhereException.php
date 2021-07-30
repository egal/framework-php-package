<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class WhereException extends Exception
{

    protected $message = 'Search Exception!';

    protected $code = 405;

}
