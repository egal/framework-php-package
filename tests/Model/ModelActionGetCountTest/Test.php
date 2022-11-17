<?php

namespace Egal\Tests\Model\ModelActionGetCountTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ModelActionGetCountTest\Models\Product;
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
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
            ['id' => 4],
        ];

        foreach ($productsAttributes as $attributes) Product::query()->create($attributes);
    }

    public function dataProvider(): array
    {
        return [
            [
                [],
                null,
                4,
            ],
            [
                [['id', 'eq', 5]],
                null,
                0,
            ],
            [
                [['id', 'eq', 1]],
                null,
                1,
            ],
            [
                [['id', 'eq', 1], 'OR', ['id', 'eq', 2]],
                null,
                2,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(?array $filter, ?string $expectException, int $equalsExpect)
    {
        $this->seedData();

        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        if ($expectException) $this->expectException($expectException);

        $actual = Product::actionGetCount($filter)['count'];

        if ($equalsExpect) $this->assertEquals($equalsExpect, $actual);
    }

}
