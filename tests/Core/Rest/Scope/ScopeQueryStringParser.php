<?php

namespace Egal\Tests\Core\Rest\Scope;

use Egal\Core\Exceptions\ScopeParseException;
use Egal\Core\Rest\Scope\Parser;
use Egal\Core\Rest\Scope\ScopeFunction;
use PHPUnit\Framework\TestCase;

class ScopeQueryStringParser extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testParsingStringQueryToArray(?string $stringQuery, array|string $expected): void
    {
        if (is_string($expected)) {
            $this->expectException($expected);
        }
        $this->assertEquals($expected, (new Parser())->parse($stringQuery));
    }

    public function dataProvider()
    {
        return [
            [
                null,
                []
            ],
            [
                "",
                []
            ],
            [
                " ",
                ScopeParseException::class
            ],
            [
                "a",
                ScopeParseException::class
            ],
            [
                "scope(",
                ScopeParseException::class
            ],
            [
                "scope()",
                [ScopeFunction::make("scope")]
            ],
            [
                "scope(a)",
                ScopeParseException::class
            ],
            [
                "scope(parameter_first = 1, parameter_second = 'second')",
                [ScopeFunction::make("scope", [["key" => "parameter_first", "value" => 1], ["key" => "parameter_second", "value" => 'second']])]
            ],
            [
                "scope(parameter_first = null, parameter_second = true)",
                [ScopeFunction::make("scope", [["key" => "parameter_first", "value" => null], ["key" => "parameter_second", "value" => true]])]
            ]
        ];
    }
}
