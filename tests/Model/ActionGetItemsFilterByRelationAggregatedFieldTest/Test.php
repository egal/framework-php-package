<?php

namespace Egal\Tests\Model\ActionGetItemsFilterByRelationAggregatedFieldTest;

use Closure;
use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Model\Exceptions\UnsupportedFieldPatternInFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterValueTypeException;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ActionGetItemsFilterByRelationAggregatedFieldTest\Models\Category;
use Egal\Tests\Model\ActionGetItemsFilterByRelationAggregatedFieldTest\Models\Product;
use Egal\Tests\PHPUnitUtil;
use Egal\Tests\TestCase;
use Mockery as m;

class Test extends TestCase
{

    use DatabaseMigrations;

    public function getDir(): string
    {
        return __DIR__;
    }

    public function seedData(): void
    {
        Category::query()->create(['id' => 1]);
        Category::query()->create(['id' => 2]);
        Product::query()->create(['id' => 1, 'category_id' => 1]);
        Product::query()->create(['id' => 2, 'category_id' => 1]);
        Product::query()->create(['id' => 3, 'category_id' => 1]);
    }

    public function dataProvider(): array
    {
        return [
            [
                [["products.exists()", "eq", false]],
                null,
                fn () => Category::query()->whereDoesntHave('products')->get()->toArray(),
                [],
            ],
            [
                [["products.exists()", "eq", true]],
                null,
                fn () => Category::query()->whereHas('products')->get()->toArray(),
                [],
            ],
            [
                [["products.exist()", "eq", true]],
                UnsupportedFieldPatternInFilterConditionException::class,
                [],
                [],
            ],
            [
                [["products.exists()", "eq", 9]],
                UnsupportedFilterValueTypeException::class,
                [],
                []
            ],
            [
                [["products.exists()", "ne", true]],
                UnsupportedFilterConditionException::class,
                [],
                [],
            ],
            [
                [["products.exists()", "foo", true]],
                UnsupportedFilterConditionException::class,
                [],
                [],
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(
        ?array  $filter,
        ?string $expectException,
                $responseExpect,
        array   $relations
    )
    {
        $this->seedData();

        $user = m::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        if ($expectException) $this->expectException($expectException);

        $actual = Category::actionGetItems(null, $relations, $filter, [])['items'];

        if ($responseExpect instanceof Closure) $responseExpect = $responseExpect();

        $this->assertEquals($responseExpect,$actual);
    }

}
