<?php

namespace Egal\Tests\Model\ActionGetItemByCustomKeyNameTest;

use Egal\Auth\Entities\Client;
use Egal\Core\Session\Session;
use Egal\Tests\Model\ActionGetItemByCustomKeyNameTest\Models\Product;
use Egal\Tests\PHPUnitUtil;
use Egal\Tests\TestCase;
use Egal\Tests\DatabaseMigrations;
use Mockery as m;

class Test extends TestCase
{

    use DatabaseMigrations;

    public function getDir(): string
    {
        return __DIR__;
    }

    public function testGetItem()
    {
        $firstProduct = new Product(['value' => '33']);
        $firstProduct->key = '1';
        $firstProduct->save();

        $secondProduct = new Product(['value' => '33']);
        $secondProduct->key = '2';
        $secondProduct->save();

        $user = m::mock(Client::class);
        $user->shouldReceive('mayOrFail')->andReturn(true);
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', $user);

        $this->assertEquals(
            $firstProduct->value,
            Product::actionGetItem($secondProduct->key)['value']
        );
    }

}
