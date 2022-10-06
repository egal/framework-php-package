<?php

namespace Egal\Core\Listeners;

abstract class EventListener
{

    abstract public function handle(): void;

}
