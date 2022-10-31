<?php

namespace Egal\Tests\Auth\ClientPolicyTest;

use Egal\Auth\Entities\Guest;
use Egal\Core\Session\Session;
use Egal\Tests\PHPUnitUtil;
use Egal\Tests\TestCase;

class Test extends TestCase
{

    public function dataProvider(): array
    {
        return [
            ['foo', false],
            ['bar', true],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(string $ability, bool $expected)
    {
        PHPUnitUtil::setProperty(app(Session::class), 'authEntity', new Guest());

        $model = new Post();

        $this->assertEquals($expected, Session::client()->may($ability, $model));
    }

}
