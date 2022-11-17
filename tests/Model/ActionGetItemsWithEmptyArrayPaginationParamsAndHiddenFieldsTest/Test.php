<?php

namespace Egal\Tests\Model\ActionGetItemsWithEmptyArrayPaginationParamsAndHiddenFieldsTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\DatabaseMigrations;
use Egal\Tests\Model\ActionGetItemsWithEmptyArrayPaginationParamsAndHiddenFieldsTest\Models\Product;
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

    public function test()
    {
        $user = Mockery::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        $actionResult = Product::actionGetItems([]);

        foreach ($actionResult['items'] as $item) {
            $item = $item->toArray();

            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('sale', $item);
            $this->assertArrayNotHasKey('count', $item);
        }
    }

}
