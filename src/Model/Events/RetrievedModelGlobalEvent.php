<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class RetrievedModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'retrieved';

}
