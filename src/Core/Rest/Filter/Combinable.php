<?php

namespace Egal\Core\Rest\Filter;

trait Combinable
{

    private Combiner $combiner;

    public function getCombiner(): Combiner
    {
        return $this->combiner;
    }

}
