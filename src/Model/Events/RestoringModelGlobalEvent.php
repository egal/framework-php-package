<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class RestoringModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'restoring';

}
