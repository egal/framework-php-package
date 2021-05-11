<?php

namespace EgalFramework\CommandLine\Tests\Stubs;

class Connection
{

    public function getSchemaBuilder()
    {
        return false;
    }

    public function connection()
    {
        return $this;
    }

    public function hasTable()
    {
        return true;
    }

}
