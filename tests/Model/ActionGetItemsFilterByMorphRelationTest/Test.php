<?php

namespace Egal\Tests\Model\ActionGetItemsFilterByMorphRelationTest;

use Carbon\Carbon;
use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ActionGetItemsFilterByMorphRelationTest\Models\Comment;
use Egal\Tests\Model\ActionGetItemsFilterByMorphRelationTest\Models\Order;
use Egal\Tests\Model\ActionGetItemsFilterByMorphRelationTest\Models\Product;
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
        Product::query()->create([
            'id' => 1,
            'name' => 'first',
        ]);
        Product::query()->create([
            'id' => 2,
            'name' => 'second',
            'sale' => 30
        ]);
        Order::query()->create(['id' => 1]);
        Comment::query()->create([
            'id' => 1,
            'commentable_type' => Product::class,
            'commentable_id' => 1,
        ]);
        Comment::query()->create([
            'id' => 2,
            'commentable_type' => Order::class,
            'commentable_id' => 1,
        ]);
        Comment::query()->create([
            'id' => 3,
            'commentable_type' => Product::class,
            'commentable_id' => 2,
        ]);
    }

    public function dataProvider()
    {
        return [
            [
                [],
                null,
                [1, 2, 3]
            ],
            [
                [
                    ['commentable.id', 'eq', 1],
                ],
                null,
                [1, 2]
            ],
            [
                [
                    ['commentable[' . Product::class . '].name', 'eq', 'first'],
                ],
                null,
                [1]
            ],
            [
                [
                    ['commentable[' . Product::class . '].sale', 'ne', null],
                ],
                null,
                [3]
            ],
            [
                [
                    ['commentable[' . Product::class . '].created_at', 'le', Carbon::now()->toDateTimeString()],
                ],
                null,
                [1, 3]
            ]
        ];
    }

    /**
     * @dataProvider dataProvider
     * @group current
     */
    public function test(?array $filter, ?string $expectException, ?array $equalsExpect)
    {
        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        $this->seedData();

        if ($expectException) $this->expectException($expectException);

        $actual = array_column(Comment::actionGetItems(null, [], $filter, [])['items'], 'id');

        if ($equalsExpect) $this->assertEquals($equalsExpect, $actual);
    }

}
