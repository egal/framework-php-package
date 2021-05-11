<?php

namespace Egal\Exception;

class IrreversibleMigrationException extends Exception
{

    protected const BASE_MESSAGE_LINE = 'Миграция необратима!';

}
