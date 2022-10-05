<?php

namespace Egal\Tests\Model;

use Egal\Model\Builder as EgalBuilder;
use Egal\Model\Model;
use Egal\Model\Model as EgalModel;
use Egal\Model\Pagination\Pagination;
use Exception;
use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Class ModelActionGetItemsTest
 * @package Egal\Tests\Model
 */
class BuilderTest extends TestCase
{

    /**
     * @dataProvider difficultWithDataProvider()
     * @param Pagination|array $pagination
     * @throws Exception
     */
    public function testDifficultPagination(Model $model, $pagination, int $equalsPerPage, int $equalsPage)
    {
        $results = collect([['test' => 'foo'], ['test' => 'bar']]);

        $query = m::mock(BaseBuilder::class);
        $query->shouldReceive('from');
        $query->shouldReceive('getCountForPagination')->once()->andReturn(10);

        $builder = m::mock(EgalBuilder::class, [$query]);
        $builder->makePartial();
        $builder->setModel($model);
        $builder->shouldReceive('forPage')->once()->andReturnSelf();
        $builder->shouldReceive('get')->once()->andReturn($results);

        if ($pagination instanceof Pagination){
            $result = $builder->difficultPaginate($pagination);
        } elseif (is_array($pagination)) {
            $result = $builder->difficultPaginateFromArray($pagination);
        } else {
            throw new Exception('Not correct $pagination parameter!');
        }

        $this->assertEquals(new LengthAwarePaginator(
            $results,
            $builder->getQuery()->getCountForPagination(),
            $equalsPerPage,
            $equalsPage,
            ['path' => '/', 'pageName' => 'page']
        ), $result);
    }

    public function difficultWithDataProvider(): array
    {
        $model = new EgalModelStub();

        return [
            // With Pagination class
            [
                $model,
                $pagination = (new Pagination())->setPage(1)->setPerPage(2),
                $pagination->getPerPage(),
                $pagination->getPage()
            ],
            [
                $model,
                $pagination = (new Pagination())->setPage(1),
                $model->getPerPage(),
                $pagination->getPage()
            ],
            [
                $model,
                $pagination = (new Pagination())->setPerPage(2),
                $pagination->getPerPage(),
                $model->getPage()
            ],
            [
                $model,
                new Pagination(),
                $model->getPerPage(),
                $model->getPage()
            ],

            // With Pagination array
            [
                $model,
                $pagination = ['page'=> 1, 'per_page' => 2],
                $pagination['per_page'],
                $pagination['page']
            ],
            [
                $model,
                $pagination = ['page'=> 1],
                $model->getPerPage(),
                $pagination['page']
            ],
            [
                $model,
                $pagination = ['per_page'=> 1],
                $pagination['per_page'],
                $model->getPage()
            ],
            [
                $model,
                [],
                $model->getPerPage(),
                $model->getPage()
            ],
        ];
    }

}

class EgalModelStub extends EgalModel
{

}
