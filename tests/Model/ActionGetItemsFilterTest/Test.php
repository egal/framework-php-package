<?php

namespace Egal\Tests\Model\ActionGetItemsFilterTest;

use Carbon\Carbon;
use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ActionGetItemsFilterTest\Models\Product;
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

    protected function seedData(): void
    {

        $productsAttributes = [
            ['id' => 1, 'name' => 'first_product', 'count' => 1],
            ['id' => 2, 'name' => 'second_product', 'count' => 2, 'sale' => 30],
            ['id' => 3, 'name' => 'product_third', 'count' => 3],
            ['id' => 4, 'name' => 'product_fourth', 'count' => 4, 'sale' => 50],
        ];

        foreach ($productsAttributes as $attributes) Product::query()->create($attributes);
    }

    public function dataProviderEq()
    {
        return [
            [
                [
                    ['name', 'eq', 'first_product'],
                ],
                null,
                [1],
            ],
            [
                [
                    ['name', 'eq', 'first_product'],
                    'OR',
                    ['name', 'eq', 'second_product'],
                ],
                null,
                [1, 2],
            ],
            [
                [
                    ['name', 'eq', 'first_product'],
                    'AND',
                    [['name', 'eq', 'first_product']]
                ],
                null,
                [1],
            ],
            [
                [['sale', 'eq', null]],
                null,
                [1, 3],
            ],
            [
                [
                    ['created_at', 'eq', Carbon::now()->addDay()->toDateTimeString()]
                ],
                null,
                [],
            ],
        ];
    }

    public function dataProviderEqi()
    {
        return [
            [
                [
                    ['name', 'eqi', 'fIrSt_PrOdUcT'],
                ],
                null,
                [1],
            ],
            [
                [
                    ['created_at', 'eqi', Carbon::now()->addDay()->toDateTimeString()]
                ],
                null,
                [],
            ],
        ];
    }

    public function dataProviderNe()
    {
        return [
            [
                [
                    ['name', 'ne', 'first_product'],
                ],
                null,
                [2, 3, 4],
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                    'AND',
                    ['name', 'ne', 'second_product'],
                ],
                null,
                [3, 4],
            ],
            [
                [
                    ['name', 'ne', 'first_product'],
                    'OR',
                    ['name', 'ne', 'second_product'],
                ],
                null,
                [1, 2, 3, 4],
            ],
            [
                [
                    ['created_at', 'ne', Carbon::now()->addDay()->toDateTimeString()]
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function dataProviderNei()
    {
        return [
            [
                [
                    ['name', 'nei', 'fIrSt_PrOdUcT'],
                ],
                null,
                [2, 3, 4],
            ],
        ];
    }

    public function dataProviderCo()
    {
        return [
            [
                [
                    ['name', 'co', 'product'],
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function dataProviderCoi()
    {
        return [
            [
                [
                    ['name', 'coi', 'pRoDuCt'],
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function dataProviderNc()
    {
        return [
            [
                [
                    ['name', 'nc', 'product'],
                ],
                null,
                [],
            ],
        ];
    }

    public function dataProviderNci()
    {
        return [
            [
                [
                    ['name', 'nci', 'pRoDuCt'],
                ],
                null,
                [],
            ],
        ];
    }

    public function dataProviderSw()
    {
        return [
            [
                [
                    ['name', 'sw', 'product'],
                ],
                null,
                [3, 4],
            ],
        ];
    }

    public function dataProviderSwi()
    {
        return [
            [
                [
                    ['name', 'swi', 'pRoDuCt'],
                ],
                null,
                [3, 4],
            ],
        ];
    }

    public function dataProviderEw()
    {
        return [
            [
                [
                    ['name', 'ew', 'product'],
                ],
                null,
                [1, 2],
            ],
        ];
    }

    public function dataProviderEwi()
    {
        return [
            [
                [
                    ['name', 'ewi', 'pRoDuCt'],
                ],
                null,
                [1, 2],
            ],
        ];
    }

    public function dataProviderGt()
    {
        return [
            [
                [
                    ['count', 'gt', 1],
                ],
                null,
                [2, 3, 4],
            ],
            [
                [
                    ['created_at', 'gt', Carbon::now()->toDateTimeString()]
                ],
                null,
                [],
            ],
        ];
    }

    public function dataProviderGe()
    {
        return [
            [
                [
                    ['count', 'ge', 1],
                ],
                null,
                [1, 2, 3, 4],
            ],
        ];
    }

    public function dataProviderLt()
    {
        return [
            [
                [
                    ['count', 'lt', 2],
                ],
                null,
                [1],
            ],
        ];
    }

    public function dataProviderLe()
    {
        return [
            [
                [
                    ['count', 'le', 2],
                ],
                null,
                [1, 2],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderEq
     * @dataProvider dataProviderEqi
     * @dataProvider dataProviderNe
     * @dataProvider dataProviderNei
     * @dataProvider dataProviderGt
     * @dataProvider dataProviderGe
     * @dataProvider dataProviderLt
     * @dataProvider dataProviderLe
     * @dataProvider dataProviderCo
     * @dataProvider dataProviderCoi
     * @dataProvider dataProviderNc
     * @dataProvider dataProviderNci
     * @dataProvider dataProviderSw
     * @dataProvider dataProviderSwi
     * @dataProvider dataProviderEw
     * @dataProvider dataProviderEwi
     */
    public function test(?array $filter, ?string $expectException, array $equalsExpect)
    {
        $this->seedData();

        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        if ($expectException) $this->expectException($expectException);

        $actual = array_column(Product::actionGetItems(null, [], $filter, [])['items'], 'id');

        if ($equalsExpect) {
            $equalsExpectAsString = implode(', ', $equalsExpect);
            $actualAsString = implode(', ', $actual);
            $this->assertEquals(
                [],
                array_diff($equalsExpect, $actual),
                "Expect: ${equalsExpectAsString}. Actual: ${actualAsString}."
            );
        }
    }

}
