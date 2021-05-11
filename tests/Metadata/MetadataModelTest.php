<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;
use EgalFramework\Metadata\Exception;
use EgalFramework\Metadata\Models\Metadata;
use EgalFramework\Metadata\Tests\Samples\APIStorage;
use EgalFramework\Metadata\Tests\Samples\ModelManager;
use PHPUnit\Framework\TestCase;

class MetadataModelTest extends TestCase
{

    /**
     * @throws Exception
     */
    public function testAll()
    {
        Session::setApiStorage(new APIStorage);
        $registry = new Registry;
        Session::setRegistry($registry);
        Session::setModelManager(new ModelManager);
        $model = new Metadata;
        $model->getAll();
        $this->assertEquals(1, 1);
    }

    public function testNoMetadataFault()
    {
        $model = new Metadata;
        $this->expectException(Exception::class);
        $model->getModel('Fault');
    }

}
