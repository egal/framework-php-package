<?php

declare(strict_types=1);

namespace Egal\Exception;

use Exception;

class IrreversibleMigrationException extends Exception
{

    protected $message = 'Migration is irreversible!';

    protected $code = 500;

}
