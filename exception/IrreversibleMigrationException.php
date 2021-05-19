<?php

namespace Egal\Exception;

use Exception;

/**
 * @deprecated
 */
class IrreversibleMigrationException extends Exception
{

    protected const BASE_MESSAGE_LINE = 'Миграция необратима!';

}
