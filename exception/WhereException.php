<?php

declare(strict_types=1);

namespace Egal\Exception;

use Exception;

/**
 * @depricated from v2.0.0.
 */
class WhereException extends Exception
{

    protected $message = 'Search exception!';

    protected $code = 405;

}
