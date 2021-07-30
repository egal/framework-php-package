<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class IrreversibleMigrationException extends Exception
{

    protected $message = 'Migration is irreversible!';

    protected $code = 500;

}
