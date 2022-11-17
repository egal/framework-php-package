<?php

declare(strict_types=1);

namespace Egal\Core\Events;

use Illuminate\Queue\SerializesModels;

abstract class Event
{

    use SerializesModels;
}
