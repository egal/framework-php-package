<?php

namespace Egal\Centrifugo;

use Egal\Model\Model;
use Illuminate\Support\Facades\Event;

abstract class CentrifugoEvent extends Event
{
    use CenrifugoPublishable;

    protected Model $entity;

    public function __construct(Model $entity)
    {
        $this->entity = $entity;
    }

}
