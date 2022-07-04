<?php

namespace Egal\Tests\Core\Rest\Select;

use Egal\Core\Exceptions\SelectParseException;
use Egal\Core\Rest\Filter\Field;
use Egal\Core\Rest\Filter\RelationField;
use Egal\Core\Rest\Select\Parser;
use PHPUnit\Framework\TestCase;

class SelectQueryStringParserTest extends TestCase
{
/**
     * @dataProvider fieldDataProvider
     * @dataProvider relationFieldDataProvider
     */
    public function testParsingStringQueryToArray(?string $stringQuery, array|string $expected): void
    {
        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $this->assertEquals($expected, (new Parser())->parse($stringQuery));
    }

    public function fieldDataProvider()
    {
        return [
            [
                null,
                [],
            ],
            [
                "",
                [],
            ],
            [
                "field_first, field_second",
                [new Field("field_first"), new Field("field_second")],
            ],
            [
                "field_first, ",
                SelectParseException::class,
            ],
            [
                " , field_second",
                SelectParseException::class,
            ],
            [
                "field_first",
                [new Field("field_first")],
            ],
        ];
    }

    public function relationFieldDataProvider()
    {
        return [
            [
                "relation.field",
                [new RelationField("field", "relation")]
            ],
            [
                "relation.field, ",
                SelectParseException::class
            ],
            [
                "relation.field, field",
                [new RelationField("field", "relation"), new Field("field")]
            ],
        ];
    }
}
