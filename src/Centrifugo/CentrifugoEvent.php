<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

use Egal\Model\Model;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Event;

abstract class CentrifugoEvent extends Event implements ShouldBroadcast
{

    use CenrifugoPublishable;

    private Model $model;

    private string $name;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

}
