<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class CreatingModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'creating';

}
