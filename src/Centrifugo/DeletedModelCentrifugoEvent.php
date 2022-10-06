<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

class DeletedModelCentrifugoEvent extends CentrifugoEvent
{

    protected string $name = 'deleted_model';

}
