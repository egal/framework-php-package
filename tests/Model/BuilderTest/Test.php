<?php

namespace Egal\Tests\Model\BuilderTest;

use Egal\Model\Builder as EgalBuilder;
use Egal\Model\Pagination\Pagination;
use Egal\Tests\Model\BuilderTest\Models\Model;
use Egal\Tests\TestCase;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery as m;

class Test extends TestCase
{

    public function dataProvider(): array
    {
        return [
            // With Pagination class
            [(new Pagination())->setPage(1)->setPerPage(2), 2, 1],
            [(new Pagination())->setPage(1), 10, 1],
            [(new Pagination())->setPerPage(2), 2, 1],
            [new Pagination(), 10, 1],

            // With Pagination array
            [['page' => 1, 'per_page' => 2], 2, 1],
            [['page' => 1], 10, 1],
            [['per_page' => 1], 1, 1],
            [[], 10, 1],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(Pagination|array $pagination, int $equalsPerPage, int $equalsPage)
    {
        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $query = m::mock(BaseBuilder::class);
        $query->shouldReceive('from');
        $query->shouldReceive('getCountForPagination')->andReturn(10);

        $builder = m::mock(EgalBuilder::class, [$query]);
        $builder->makePartial();
        $builder->setModel(new Model());
        $builder->shouldReceive('forPage')->andReturnSelf();
        $builder->shouldReceive('get')->andReturn($results);

        $result = $pagination instanceof Pagination
            ? $builder->difficultPaginate($pagination)
            : $builder->difficultPaginateFromArray($pagination);

        $this->assertEquals(new LengthAwarePaginator(
            $results,
            $builder->getQuery()->getCountForPagination(),
            $equalsPerPage,
            $equalsPage,
            ['path' => 'http://:', 'pageName' => 'page'],
        ), $result);
    }

}
