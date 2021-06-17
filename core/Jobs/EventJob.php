<?php

namespace Egal\Core\Jobs;

use Egal\Core\Handlers\EventHandler;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;

class EventJob extends Job
{
    /**
     * @param RabbitMQJob $rabbitMQJob
     * @param array $payload
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $payload)
    {
        $rabbitMQJob->delete();
        (new EventHandler())->handle($payload);
    }
}
