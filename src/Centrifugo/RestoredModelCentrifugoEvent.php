<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

class RestoredModelCentrifugoEvent extends CentrifugoEvent
{

    protected string $name = 'restored_model';

}
