<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Common\Interfaces\AppMenuInterface;

class AppMenu implements AppMenuInterface
{
    public function build(): array
    {
        return [];
    }
}
