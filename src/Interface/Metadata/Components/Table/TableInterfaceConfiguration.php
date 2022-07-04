<?php

namespace Egal\Interface\Metadata\Components\Table;

use Egal\Interface\Metadata\Configuration;

class TableInterfaceConfiguration extends Configuration
{

    public static function make():self
    {
        return new self();
    }
}
