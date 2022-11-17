<?php

declare(strict_types=1);

namespace Egal\Core\Listeners;

abstract class EventListener
{

    abstract public function handle(): void;

}
