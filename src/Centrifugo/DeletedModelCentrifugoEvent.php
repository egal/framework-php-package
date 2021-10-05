<?php

namespace Egal\Centrifugo;

class DeletedModelCentrifugoEvent extends CentrifugoEvent
{

    protected string $name = 'deleted';

}
