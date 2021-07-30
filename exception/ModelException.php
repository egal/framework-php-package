<?php

declare(strict_types=1);

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class ModelException extends Exception
{

    protected $message = 'Model Exception!';

    protected $code = 500;

}
