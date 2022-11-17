<?php

declare(strict_types=1);

namespace Egal\Model\Events;

use Egal\Core\Events\GlobalEvent;

class UpdatingModelGlobalEvent extends GlobalEvent
{

    protected string $message = 'updating';

}
