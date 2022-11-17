<?php

namespace Egal\Tests\Model\ActionsMaxCountEntitiesToProcessTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\DatabaseMigrations;
use Egal\Model\Exceptions\ExceedingTheLimitCountEntitiesForManipulationException;
use Egal\Tests\Model\ActionsMaxCountEntitiesToProcessTest\Models\Order;
use Egal\Tests\Model\ActionsMaxCountEntitiesToProcessTest\Models\Product;
use Egal\Tests\PHPUnitUtil;
use Egal\Tests\TestCase;
use Mockery;

class Test extends TestCase
{
    use DatabaseMigrations;

    public function getDir(): string
    {
        return __DIR__;
    }

    public function dataProvider()
    {
        return [
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10']
                ],
                Product::class,
                null,
            ],
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10'],
                    ['name' => '11']
                ],
                Product::class,
                ExceedingTheLimitCountEntitiesForManipulationException::class,
            ],
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10'],
                    ['name' => '11'],
                    ['name' => '12'],
                    ['name' => '13'],
                    ['name' => '14'],
                    ['name' => '15']
                ],
                Order::class,
                null,
            ],
            [
                [
                    ['name' => '1'],
                    ['name' => '2'],
                    ['name' => '3'],
                    ['name' => '4'],
                    ['name' => '5'],
                    ['name' => '6'],
                    ['name' => '7'],
                    ['name' => '8'],
                    ['name' => '9'],
                    ['name' => '10'],
                    ['name' => '11'],
                    ['name' => '12'],
                    ['name' => '13'],
                    ['name' => '14'],
                    ['name' => '15'],
                    ['name' => '16']
                ],
                Order::class,
                ExceedingTheLimitCountEntitiesForManipulationException::class,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($data, $model, $expectException)
    {
        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        if ($expectException) $this->expectException($expectException);

        $model::actionCreateMany($data);
    }

}
