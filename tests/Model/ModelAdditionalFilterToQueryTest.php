<?php

namespace Egal\Tests\Model;

use Egal\Model\Builder;
use Egal\Model\Model;
use Egal\Tests\PHPUnitUtil;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Mockery as m;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ModelAdditionalFilterToQueryTest extends TestCase
{

    public function testModelMakeIsInstanceForAction()
    {
        $model = m::mock(ModelAdditionalFilterToQueryTestStub::class . '[*]');

        PHPUnitUtil::callMethod($model, 'makeIsInstanceForAction');

        $this->assertTrue(PHPUnitUtil::getProperty($model, 'isInstanceForAction'));
    }

    public function testModelNewQueryWithInstanceForAction()
    {
        $query = m::mock(BaseBuilder::class);
        $query->shouldReceive('from')->with('foo_table');

        $builder = new Builder($query);

        /** @var ModelAdditionalFilterToQueryTestStub|MockInterface|LegacyMockInterface $model */
        $model = m::mock(ModelAdditionalFilterToQueryTestStub::class . '[newQueryForAction]');
        $model->shouldAllowMockingProtectedMethods();
        $model->shouldReceive('newQueryForAction')->once()->andReturn($builder);

        PHPUnitUtil::callMethod($model, 'makeIsInstanceForAction');

        $this->assertEquals($builder, $model->newQuery());
    }

    public function testModelNewQueryWithNewQueryForActionMethod()
    {
        /** @var ModelAdditionalFilterToQueryTestStub|MockInterface|LegacyMockInterface $model */
        $model = m::mock(ModelAdditionalFilterToQueryTestStub::class . '[*]');

        PHPUnitUtil::callMethod($model, 'makeIsInstanceForAction');

        $this->assertEquals('bar', $model->newQuery()->foo);
    }

}

class ModelAdditionalFilterToQueryTestStub extends Model
{

    public function newQueryForAction(): Builder
    {
        $query = m::mock(BaseBuilder::class);
        $query->shouldReceive('from')->with('foo_table');

        $builder = new Builder($query);
        $builder->foo = 'bar';

        return $builder;
    }

}
