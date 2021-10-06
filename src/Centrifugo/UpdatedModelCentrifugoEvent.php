<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

class UpdatedModelCentrifugoEvent extends CentrifugoEvent
{

    protected string $name = 'updated_model';

}
