<?php

namespace Egal\Tests\Model\ActionsFilterValidationTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Model\Exceptions\FieldNotFoundException;
use Egal\Model\Exceptions\UnsupportedFilterConditionException;
use Egal\Model\Exceptions\UnsupportedFilterValueTypeException;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ActionsFilterValidationTest\Models\Model;
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
            ['id' => 1, 'string' => 'first', 'integer' => 1, 'numeric' => 1.1, 'boolean' => true, 'array' => ['first'], 'json' => '{"first":["first"]}'],
            ['id' => 2, 'string' => 'second', 'integer' => 2, 'numeric' => 2.2, 'boolean' => false, 'array' => ['second'], 'json' => '{"second":["second"]}'],
            ['id' => 3, 'string' => 'third', 'integer' => 3, 'numeric' => 3.3, 'boolean' => true, 'array' => ['third'], 'json' => '{"third":["third"]}'],
            ['id' => 4, 'string' => 'fourth', 'integer' => 4, 'numeric' => 4.4, 'boolean' => false, 'array' => ['fourth'], 'json' => '{"fourth":["fourth"]}'],
        ];

        foreach ($productsAttributes as $attributes) Model::query()->create($attributes);
    }

    public function dataProvider(): array
    {
        return [
            [
                [['foo', 'eq', 'bar']],
                FieldNotFoundException::class,
            ],
            [
                [['string', 'eq', 'bar']],
                null,
            ],
            [
                [['string', 'eq', 34]],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['string', 'edq', 'bar']],
                UnsupportedFilterConditionException::class,
            ],
//            TODO: Restore.
//            [
//                [['created_at', 'eq', '2021-10-00T11:24:07.000000Z']],
//                UnsupportedFilterValueTypeException::class,
//            ],
            [
                [['created_at', 'eq', '2021-10-01T11:24:07.000000Z']],
                null,
            ],
            [
                [['integer', 'eq', 2]],
                null,
            ],
            [
                [['integer', 'eq', 'two']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['boolean', 'eq', true]],
                null,
            ],
            [
                [['boolean', 'eq', 'true']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['numeric', 'eq', 1.1]],
                null,
            ],
            [
                [['numeric', 'eq', '0x539']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['array', 'foo', ['foo']]],
                null,
            ],
            [
                [['array', 'eq', 'foo']],
                UnsupportedFilterValueTypeException::class,
            ],
            [
                [['json', 'foo', '{"fourth":["fourth"]}']],
                null,
            ],
            [
                [['json', 'eq', 'foo']],
                UnsupportedFilterValueTypeException::class,
            ],
//            TODO: Restore.
//            [
//                [['fake', 'eq', 'foo']],
//                FieldNotFoundException::class,
//            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(array $filter, ?string $expectException)
    {
        $this->seedData();

        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        if ($expectException) $this->expectException($expectException);

        Model::actionGetItems(null, [], $filter, []);
    }

}
