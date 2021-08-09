<?php

namespace Egal\Tests\Model;

use Egal\Model\Filter\FilterPart;
use PHPUnit\Framework\TestCase;

class ModelFilterFilterPartTest extends TestCase
{

    public function simpleDataProvider()
    {
        return [
            [
                [["foo", "eq", "bar"]],
                null
            ],
            [
                [[
                    ["foo", "eq", "bar"],
                    "and",
                    ["foo", "eq", "bar"],
                ]],
                null
            ],
            [
                [[
                    ["foo", "eq", "bar"],
                    "and",
                    [[[[[[["foo", "eq", "bar"]]]]]]],
                ]],
                null
            ],
            [
                [[[[[["foo", "eq", "bar"]]]]]],
                null
            ],
        ];
    }

    /**
     * @dataProvider simpleDataProvider()
     */
    public function testSimple(array $array, ?string $expectException)
    {
        if ($expectException) {
            $this->expectException($expectException);
        }

        $result = FilterPart::fromArray($array);

        if (!$expectException) {
            $this->assertNotNull($result);
        }
    }

}
