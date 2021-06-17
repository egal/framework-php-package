<?php

namespace Egal\Core\Jobs;

use Egal\Core\Exceptions\ActionCallException;
use Egal\Core\Exceptions\InitializeMessageFromArrayException;
use Egal\Core\Exceptions\UndefinedTypeOfMessageException;
use Egal\Core\Handlers\ActionHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ReflectionException;

/**
 * Class ActionJob
 * @package Egal\Core\Jobs
 */
class ActionJob extends Job
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param RabbitMQJob $rabbitMQJob
     * @param array $data
     * @throws ActionCallException
     * @throws BindingResolutionException
     * @throws InitializeMessageFromArrayException
     * @throws ReflectionException
     * @throws UndefinedTypeOfMessageException
     * @throws \Egal\Auth\Exceptions\InitializeServiceServiceTokenException
     * @throws \Egal\Auth\Exceptions\InitializeUserServiceTokenException
     * @throws \Egal\Auth\Exceptions\TokenExpiredException
     * @throws \Egal\Auth\Exceptions\UndefinedTokenTypeException
     * @throws \Egal\Core\Exceptions\TokenSignatureInvalidException
     */
    public function handle(RabbitMQJob $rabbitMQJob, array $data): void
    {
        $rabbitMQJob->delete();
        (new ActionHandler())->handle($data);
    }
}
