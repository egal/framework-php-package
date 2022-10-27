<?php

namespace Egal\Tests;

use Egal\Core\Application;
use Egal\Core\ServiceProvider;
use Laravel\Lumen\Testing\TestCase as LumenTestCase;

class TestCase extends LumenTestCase
{

    public function createApplication()
    {
        $app = new Application(dirname(__DIR__));
        $app->withFacades();
        $app->withEloquent();
        $app->register(ServiceProvider::class);

        return $app;
    }

}
