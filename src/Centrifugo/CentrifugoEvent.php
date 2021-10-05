<?php

namespace Egal\Centrifugo;

use Egal\Model\Model;
use Illuminate\Support\Facades\Event;

abstract class CentrifugoEvent extends Event
{
    use CenrifugoPublishable;

    private Model $model;
    private string $name;

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->name = snake_case(get_class_short_name($this));
    }
}
