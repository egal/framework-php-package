<?php

declare(strict_types=1);

namespace Egal\Exception;

use Exception;

/**
 * @depricated from v2.0.0.
 */
class ModelException extends Exception
{

    protected $message = 'Model exception!';

    protected $code = 500;

}
