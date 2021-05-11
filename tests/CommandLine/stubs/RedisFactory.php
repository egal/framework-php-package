<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

use Illuminate\Contracts\Redis\Factory;

class RedisFactory implements Factory
{

    public function connection($name = null)
    {
        return new RedisDB;
    }

}
