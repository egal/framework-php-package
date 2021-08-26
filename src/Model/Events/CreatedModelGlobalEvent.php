<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class CreatedModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'created';

}
