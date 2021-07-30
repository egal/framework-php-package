<?php

declare(strict_types=1);

namespace Egal\Exception;

use Exception;

class WhereException extends Exception
{

    protected $message = 'Search Exception!';

    protected $code = 405;

}
