<?php

declare(strict_types=1);

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class SavingModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'saving';

}
