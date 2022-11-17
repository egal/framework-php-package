<?php

namespace Egal\Tests\Model\ActionGetItemsFilterRelationsTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ActionGetItemsFilterRelationsTest\Models\Category;
use Egal\Tests\Model\ActionGetItemsFilterRelationsTest\Models\Product;
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
        Category::query()->create(['id' => 1]);
        Product::query()->create(['id' => 1, 'category_id' => 1]);
        Product::query()->create(['id' => 2, 'category_id' => 1]);
        Product::query()->create(['id' => 3, 'category_id' => 1]);
    }

    public function dataProvider(): array
    {
        return [
            [
                ['products'],
                [1, 2, 3],
            ],
            [
                ['products' => []],
                [1, 2, 3],
            ],
            [
                ['products' => ['filter' => [['id', 'eq', 1]]]],
                [1],
            ],
            [
                ['products' => ['filter' => [['id', 'eq', 1], 'OR', ['id', 'eq', 2]]]],
                [1, 2],
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(array $relations, array $equalsExpect)
    {
        $this->seedData();

        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        $actionResult = Category::actionGetItems(null, $relations, [['id', 'eq', 1]], []);
        $actual = array_column($actionResult['items'][0]['products'], 'id');

        if ($equalsExpect) $this->assertEquals($equalsExpect, $actual);
    }

}

