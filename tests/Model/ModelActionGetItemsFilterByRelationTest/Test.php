<?php

namespace Egal\Tests\Model\ModelActionGetItemsFilterByRelationTest;

use Carbon\Carbon;
use Closure;
use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Model\Builder;
use Egal\Model\Exceptions\RelationNotFoundException;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ModelActionGetItemsFilterByRelationTest\Models\Category;
use Egal\Tests\Model\ModelActionGetItemsFilterByRelationTest\Models\Product;
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

    protected function seedData(): void
    {
        Category::create([
            'id' => 1,
            'name' => 'first_category',
        ]);
        Category::create([
            'id' => 2,
            'name' => 'first_category',
            'sale' => 30
        ]);
        Product::create([
            'id' => 1,
            'name' => 'first_product',
            'category_id' => 1,
        ]);
        Product::create([
            'id' => 2,
            'name' => 'second_product',
            'category_id' => 2,
        ]);
    }

    public function dataProvider(): array
    {
        return [
            [
                [['foo.bar', 'eq', 1]],
                RelationNotFoundException::class,
            ],
            [
                [['category.id', 'eq', 3]],
                [],
            ],
            [
                [['category.id', 'eq', 1]],
                fn() => Product::query()->whereHas('category', function (Builder $query) {
                    $query->where('id', '=', 1);
                })->get()->toArray(),
            ],
            [
                [['category_with_word.id', 'eq', 1000]],
                [],
            ],
            [
                [['category.id', 'eq', 1000], 'AND', ['category_with_word.id', 'eq', 1000]],
                [],
            ],
            [
                [['category.id', 'eq', 1], 'OR', ['category_with_word.id', 'eq', 1000]],
                fn() => Product::query()->whereHas('category', function (Builder $query) {
                    $query->where('id', '=', 1);
                })->get()->toArray(),
            ],
            [
                [['category.sale', 'eq', null]],
                fn() => Product::query()->whereHas('category', function (Builder $query) {
                    $query->where('sale', '=', null);
                })->get()->toArray(),
            ],
            [
                [['category_with_word.created_at', 'le', Carbon::now()->toDateTimeString()]],
                fn() => Product::query()->whereHas('category', function (Builder $query) {
                    $query->where('created_at', '<=', Carbon::now()->toDateTimeString());
                })->get()->toArray(),
            ],
        ];
    }

    /**
     * @dataProvider dataProvider()
     * @param class-string|array|Closure $expect
     */
    public function test(array $filter, string|array|Closure $expect)
    {
        $this->seedData();

        $user = m::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        if (is_string($expect)) $expectException = $expect;
        $assertEquals = $expect instanceof Closure ? $expect() : $expect;

        if (isset($expectException)) $this->expectException($expectException);

        $actual = Product::actionGetItems(null, [], $filter, [])['items'];

        if (isset($assertEquals)) $this->assertEquals($assertEquals, $actual);
    }

}
