<?php

namespace Egal\Tests\Model;

use Egal\Model\With\Collection;
use Egal\Model\With\Relation;
use PHPUnit\Framework\TestCase;

class ModelHelpersTest extends TestCase
{

    public function dataProvider(): array
    {
        return [
            [
                [new Relation(), new Relation(), new Relation()],
                Relation::class,
                true,
            ],
            [
                [new Relation(), new Relation(), new Collection()],
                Relation::class,
                false,
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test($data, $class, $expectAssertEquals)
    {
        $this->assertEquals($expectAssertEquals, is_array_of_classes($data, $class));
    }

}
