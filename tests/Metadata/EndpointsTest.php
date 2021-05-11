<?php

namespace EgalFramework\Metadata\Tests;

use EgalFramework\Common\Interfaces\APIContainer\MethodInterface;
use EgalFramework\Common\Interfaces\APIContainer\StorageInterface;
use EgalFramework\Metadata\Endpoints;
use EgalFramework\Metadata\Tests\Samples\Method;
use EgalFramework\Metadata\Tests\Samples\Model;
use PHPUnit\Framework\TestCase;

class EndpointsTest extends TestCase
{

    private StorageInterface $storage;

    private MethodInterface $sampleMethod;

    protected function setUp(): void
    {
        parent::setUp();
        $builder = $this->getMockBuilder(StorageInterface::class);
        $storage = $builder
            ->setMethods([
                'getClass',
                'save',
                'saveClass',
                'getMethod',
                'removeMethod',
                'saveMethod',
                'removeClass',
                'removeAll',
            ])
            ->disableOriginalConstructor()
            ->getMock();
        $sampleModel = new Model;
        $sampleModel->name = 'model';
        $this->sampleMethod = new Method;
        $this->sampleMethod->name = 'methodName';
        $sampleModel->setMethod($this->sampleMethod->name, $this->sampleMethod);
        $storage
            ->method('getClass')
            ->willReturn($sampleModel);
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->storage = $storage;
    }

    /**
     * @param array $methodRoles
     * @param array $userRoles '
     * @param bool $success
     * @dataProvider dataProvider
     */
    public function testEndpoints(array $methodRoles, array $userRoles, bool $success)
    {
        $this->sampleMethod->roles = $methodRoles;
        $endpoints = new Endpoints($this->storage);
        $endpoints->addClass('model', $userRoles);
        if ($success) {
            $this->assertEquals(['model' => ['methodName']], $endpoints->endpoints);
        } else {
            $this->assertEquals([], $endpoints->endpoints);
        }
    }

    public function dataProvider()
    {
        return [
            [
                ['admin'],
                ['user'],
                false,
            ],
            [
                ['admin', 'user'],
                ['user', 'www'],
                true
            ],
            [
                [],
                [],
                false,
            ],
            [
                ['admin'],
                [],
                false,
            ],
            [
                [],
                ['admin'],
                false,
            ],
        ];
    }

}
