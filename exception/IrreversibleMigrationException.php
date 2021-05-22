<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class IrreversibleMigrationException extends Exception
{

    protected $message = 'Миграция необратима!';
    protected $code = 500;

}
