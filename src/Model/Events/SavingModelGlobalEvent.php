<?php

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class SavingModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'saving';

}
