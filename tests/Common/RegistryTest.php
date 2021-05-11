<?php

namespace EgalFramework\Common\Tests;

use EgalFramework\Common\Registry;
use PHPUnit\Framework\TestCase;

class RegistryTest extends TestCase
{

    public function testRegistry()
    {
        $registry = new Registry();
        $this->assertNull($registry->get('test'));
        $registry->set('test', 'testValue');
        $this->assertEquals('testValue', $registry->get('test'));
    }

}
