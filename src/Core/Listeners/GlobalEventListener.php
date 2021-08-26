<?php

namespace Egal\Core\Listeners;

abstract class GlobalEventListener
{

    abstract public function handle(array $data): void;

}
