<?php

declare(strict_types=1);

namespace Egal\Model\Exceptions;

use Exception;

class RelationNotFoundException extends Exception
{

    protected $message = 'Relation not found!';

}
