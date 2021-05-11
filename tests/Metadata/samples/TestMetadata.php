<?php

namespace EgalFramework\Metadata\Tests\Samples;

use EgalFramework\Metadata\Metadata as AMetadata;

class TestMetadata extends AMetadata
{

    protected string $label = '';

    public function __construct()
    {
        $this->data = [];
        parent::__construct();
    }

}
