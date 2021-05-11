<?php

namespace EgalFramework\Common\Tests;

use EgalFramework\Common\Interfaces\APIContainer\ParserInterface;
use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;
use EgalFramework\Common\Interfaces\AppMenuInterface;
use EgalFramework\Common\Interfaces\FilterQueryInterface;
use EgalFramework\Common\Interfaces\Kerberos\KerberosInterface;
use EgalFramework\Common\Interfaces\MessageInterface;
use EgalFramework\Common\Interfaces\ModelManagerInterface;
use EgalFramework\Common\Interfaces\QueueInterface;
use EgalFramework\Common\Registry;
use EgalFramework\Common\Session;
use EgalFramework\Common\Tests\Samples\ModelManager;
use EgalFramework\Common\Tests\Samples\TestMetadata;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{

    public function testMessage()
    {
        $this->assertNull(Session::getMessage());
        /** @var MessageInterface $message */
        $message = $this->getMock(MessageInterface::class);
        Session::setMessage($message);
        $this->assertInstanceOf(MessageInterface::class, Session::getMessage());
    }

    public function testModelManager()
    {
        /** @var ModelManagerInterface $modelManager */
        $modelManager = $this->getMock(ModelManagerInterface::class);
        Session::setModelManager($modelManager);
        $this->assertInstanceOf(ModelManagerInterface::class, Session::getModelManager());
    }

    public function testFilterQuery()
    {
        /** @var FilterQueryInterface $filterQuery */
        $filterQuery = $this->getMock(FilterQueryInterface::class);
        Session::setFilterQuery($filterQuery);
        $this->assertInstanceOf(FilterQueryInterface::class, Session::getFilterQuery());
    }

    public function testValidationCallback()
    {
        $callback = function () {
            return 'asd';
        };
        Session::setValidateCallback($callback);
        $this->assertEquals('asd', Session::getValidateCallback()());
    }

    public function testMetadata()
    {
        Session::setModelManager(new ModelManager);
        $this->assertInstanceOf(TestMetadata::class, Session::getMetadata('TestMetadata'));
    }

    public function testRegistry()
    {
        Session::setRegistry(new Registry());
        $this->assertInstanceOf(Registry::class, Session::getRegistry());
    }

    public function testMenu()
    {
        /** @var AppMenuInterface $menu */
        $menu = $this->getMock(AppMenuInterface::class);
        Session::setMenu($menu);
        $this->assertInstanceOf(AppMenuInterface::class, Session::getMenu());
    }

    public function testQueue()
    {
        /** @var QueueInterface $queue */
        $queue = $this->getMock(QueueInterface::class);
        Session::setQueue($queue);
        $this->assertInstanceOf(QueueInterface::class, Session::getQueue());
    }

    public function testApiStorage()
    {
        /** @var StorageInterface $apiStorage */
        $apiStorage = $this->getMock(StorageInterface::class);
        Session::setApiStorage($apiStorage);
        $this->assertInstanceOf(StorageInterface::class, Session::getApiStorage());
    }

    public function testApiParser()
    {
        /** @var ParserInterface $parser */
        $parser = $this->getMock(ParserInterface::class);
        Session::setApiParser($parser);
        $this->assertInstanceOf(ParserInterface::class, Session::getApiParser());
    }

    public function testKerberosApi()
    {
        /** @var KerberosInterface $kerberos */
        $kerberos = $this->getMock(KerberosInterface::class);
        Session::setKerberosApi($kerberos);
        $this->assertInstanceOf(KerberosInterface::class, Session::getKerberosApi());
    }

    public function getMock(string $className)
    {
        $builder = $this->getMockBuilder($className);
        return $builder
            ->getMock();
    }

}
