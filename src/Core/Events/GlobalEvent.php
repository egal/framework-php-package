<?php

declare(strict_types=1);

namespace Egal\Core\Events;

use Egal\Core\Messages\EventMessage;
use Egal\Model\Model;
use Illuminate\Queue\SerializesModels;

abstract class GlobalEvent
{

    use SerializesModels;

    protected Model $entity;
    protected string $message;

    public function __construct(Model $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @throws \ReflectionException
     */
    public function publish()
    {
        $message = new EventMessage(
            $this->entity->getModelMetadata()->getModelShortName(),
            $this->entity->getKey() ?? '*',
            $this->message,
            $this->entity->toArray(),
        );
        $message->publish();
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

}
