<?php

namespace Egal\Tests\Auth\EntitiesAuthorizedClientHasRolesTest;

use Egal\Auth\Entities\User;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class Test extends TestCase
{

    public function dataProvider(): array
    {
        return [
            [
                [],
                [],
                true,
            ],
            [
                ['first'],
                [],
                true,
            ],
            [
                ['first'],
                ['first'],
                true,
            ],
            [
                [],
                ['first'],
                false,
            ],
            [
                ['second'],
                ['first'],
                false,
            ],
            [
                ['second'],
                ['first', 'second'],
                false,
            ],
            [
                ['first', 'second'],
                ['first', 'second'],
                true,
            ],
            [
                ['second', 'first'],
                ['first', 'second'],
                true,
            ],
            [
                ['second', 'first'],
                ['first'],
                true,
            ],
            [
                ['second', 'first'],
                ['second'],
                true,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(array $existsRoles, array $checkRoles, bool $expected)
    {
        /** @var User|\Mockery\MockInterface|\Mockery\LegacyMockInterface $client */
        $client = m::mock(User::class)->makePartial();
        $client->shouldReceive('getRoles')->andReturn($existsRoles);

        $this->assertEquals($expected, $client->hasRoles($checkRoles));
    }

}
