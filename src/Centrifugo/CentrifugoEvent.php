<?php

namespace Egal\Centrifugo;

use Egal\Model\Model;
use Illuminate\Support\Facades\Event;

abstract class CentrifugoEvent extends Event
{
    use CenrifugoPublishable;

    private Model $entity;
    private string $name;
}
