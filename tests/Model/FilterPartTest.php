<?php

namespace Egal\Tests\Model;

use Egal\Model\Filter\FilterPart;
use PHPUnit\Framework\TestCase;

class FilterPartTest extends TestCase
{

    public function dataProvider(): array
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
     * @dataProvider dataProvider
     */
    public function test(array $array, ?string $expectException)
    {
        if ($expectException) $this->expectException($expectException);

        $result = FilterPart::fromArray($array);

        if (!$expectException) $this->assertNotNull($result);
    }

}
