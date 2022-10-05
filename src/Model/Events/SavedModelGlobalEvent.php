<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class SavedModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'saved';

}
