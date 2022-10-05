<?php

declare(strict_types=1);

namespace Egal\Centrifugo;

class RestoringModelCentrifugoEvent extends CentrifugoEvent
{

    protected string $name = 'restoring_model';

}
