<?php

namespace Egal\Tests;

use Laravel\Lumen\Testing\TestCase as LumenTestCase;

class TestCase extends LumenTestCase
{

    public function createApplication()
    {
        return require __DIR__ . '/bootstrap.php';
    }

}
