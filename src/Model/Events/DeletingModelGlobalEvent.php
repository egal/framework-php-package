<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class DeletingModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'deleting';

}
