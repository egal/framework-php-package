<?php

namespace Egal\Core\Handlers;

interface HandlerInterface
{
    /**
     * @param array $data
     * @return void
     */
    public function handle(array $data);
}
