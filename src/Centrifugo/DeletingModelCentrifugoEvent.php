<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

class DeletingModelCentrifugoEvent extends CentrifugoEvent
{

    protected string $name = 'deleting_model';

}
