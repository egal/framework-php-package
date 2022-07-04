<?php

namespace Egal\Tests\Core\Rest\Filter;

use Egal\Core\Exceptions\FilterParseException;
use Egal\Core\Rest\Filter\Combiner;
use Egal\Core\Rest\Filter\FieldCondition;
use Egal\Core\Rest\Filter\Field;
use Egal\Core\Rest\Filter\MorphRelationField;
use Egal\Core\Rest\Filter\Operator;
use Egal\Core\Rest\Filter\Parser;
use Egal\Core\Rest\Filter\Query;
use Egal\Core\Rest\Filter\RelationField;
use PHPUnit\Framework\TestCase;

class FilterQueryStringParserTest extends TestCase
{

    /**
     * @dataProvider fieldDataProvider
     * @dataProvider relationFieldDataProvider
     * @dataProvider morphRelationFieldDataProvider
     */
    public function testParsingStringQueryToFilterQuery(?string $stringQuery, Query|string $expected): void
    {
        if (is_string($expected)) {
            $this->expectException($expected);
        }

        $this->assertEquals($expected, (new Parser())->parse($stringQuery));
    }

    public function fieldDataProvider(): array
    {
        $field = new Field('field');

        $firstField = new Field('first');
        $secondField = new Field('second');
        $thirdField = new Field('third');
        $fourthField = new Field('fourth');
        $fifthField = new Field('fifth');
        $sixthField = new Field('sixth');

        return [
            [
                null,
                Query::make([], Combiner::And),
            ],
            [
                "",
                Query::make([], Combiner::And),
            ],
            [
                " ",
                FilterParseException::class,
            ],
            [
                "a",
                FilterParseException::class,
            ],
            [
                "field eq",
                FilterParseException::class,
            ],
            [
                "eq value",
                FilterParseException::class,
            ],
            [
                "eq",
                FilterParseException::class,
            ],
            [
                "field eq value",
                FilterParseException::class,
            ],
            [
                "field eq true",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, true, Combiner::And),
                ], Combiner::And),
            ],
            [
                "field not eq true",
                Query::make([
                    FieldCondition::make($field, Operator::NotEquals, true, Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq false",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, false, Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq null",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, null, Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq 1",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, 1, Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq 1.5",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, 1.5, Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq 'string'",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, 'string', Combiner::And),
                ], Combiner::And),
            ],
            [
                "first eq 1 and second eq 2",
                Query::make([
                    FieldCondition::make($firstField, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($secondField, Operator::Equals, 2, Combiner::And),
                ], Combiner::And),
            ],
            [
                "first eq 1 and second eq 2 or third eq 3",
                Query::make([
                    FieldCondition::make($firstField, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($secondField, Operator::Equals, 2, Combiner::And),
                    FieldCondition::make($thirdField, Operator::Equals, 3, Combiner::Or),
                ], Combiner::And),
            ],
            [
                "first eq 1 and second eq 2 and third eq 3 and (fourth eq 4 or fifth eq 5) and sixth eq 6",
                Query::make([
                    FieldCondition::make($firstField, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($secondField, Operator::Equals, 2, Combiner::And),
                    FieldCondition::make($thirdField, Operator::Equals, 3, Combiner::And),
                    Query::make([
                        FieldCondition::make($fourthField, Operator::Equals, 4, Combiner::And),
                        FieldCondition::make($fifthField, Operator::Equals, 5, Combiner::Or),
                    ], Combiner::And),
                    FieldCondition::make($sixthField, Operator::Equals, 6, Combiner::And),
                ], Combiner::And),
            ],
            [
                "first eq 1 and second eq 2 and third eq 3 and (fourth eq 4 or (fifth eq 5 or sixth eq 6))",
                Query::make([
                    FieldCondition::make($firstField, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($secondField, Operator::Equals, 2, Combiner::And),
                    FieldCondition::make($thirdField, Operator::Equals, 3, Combiner::And),
                    Query::make([
                        FieldCondition::make($fourthField, Operator::Equals, 4, Combiner::And),
                        Query::make([
                            FieldCondition::make($fifthField, Operator::Equals, 5, Combiner::And),
                            FieldCondition::make($sixthField, Operator::Equals, 6, Combiner::Or),
                        ], Combiner::Or),
                    ], Combiner::And),
                ], Combiner::And),
            ],
        ];
    }

    public function relationFieldDataProvider()
    {
        $field = new Field('field');
        $relationField = new RelationField('field', 'relation');

        $firstField = new Field('first');
        $secondField = new Field('second');
        $fourthField = new Field('fourth');
        $sixthField = new Field('sixth');

        return [
            [
                "relation.field eq",
                FilterParseException::class,
            ],
            [
                "relation.field eq null",
                Query::make([
                    FieldCondition::make($relationField, Operator::Equals, null, Combiner::And),
                ], Combiner::And),
            ],
            [
                "relation.field eq 'string'",
                Query::make([
                    FieldCondition::make($relationField, Operator::Equals, 'string', Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq 1 and relation.field eq 2",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($relationField, Operator::Equals, 2, Combiner::And),
                ], Combiner::And),
            ],
            [
                "first eq 1 and second eq 2 and relation.field eq 3 and (fourth eq 4 or (relation.field eq 5 or sixth eq 6))",
                Query::make([
                    FieldCondition::make($firstField, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($secondField, Operator::Equals, 2, Combiner::And),
                    FieldCondition::make($relationField, Operator::Equals, 3, Combiner::And),
                    Query::make([
                        FieldCondition::make($fourthField, Operator::Equals, 4, Combiner::And),
                        Query::make([
                            FieldCondition::make($relationField, Operator::Equals, 5, Combiner::And),
                            FieldCondition::make($sixthField, Operator::Equals, 6, Combiner::Or),
                        ], Combiner::Or),
                    ], Combiner::And),
                ], Combiner::And),
            ],
        ];
    }

    public function morphRelationFieldDataProvider()
    {
        $field = new Field('field');
        $relationField = new RelationField('field', 'relation');
        $morphRelationField = new MorphRelationField('field', 'relation', ['type_one','type_two']);

        $firstField = new Field('first');
        $sixthField = new Field('sixth');

        return [
            [
                "relation[type_one,type_two].field eq",
                FilterParseException::class,
            ],
            [
                "relation[type_one,type_two].field eq null",
                Query::make([
                    FieldCondition::make($morphRelationField, Operator::Equals, null, Combiner::And),
                ], Combiner::And),
            ],
            [
                "relation[type_one,type_two].field eq 'string'",
                Query::make([
                    FieldCondition::make($morphRelationField, Operator::Equals, 'string', Combiner::And),
                ], Combiner::And),
            ],
            [
                "field eq 1 and relation.field eq 2 or relation[type_one,type_two].field eq 3",
                Query::make([
                    FieldCondition::make($field, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($relationField, Operator::Equals, 2, Combiner::And),
                    FieldCondition::make($morphRelationField, Operator::Equals, 3, Combiner::Or),
                ], Combiner::And),
            ],
            [
                "first eq 1 and relation[type_one,type_two].field eq 2 and relation.field eq 3 and (relation[type_one,type_two].field eq 4 or (relation.field eq 5 or sixth eq 6))",
                Query::make([
                    FieldCondition::make($firstField, Operator::Equals, 1, Combiner::And),
                    FieldCondition::make($morphRelationField, Operator::Equals, 2, Combiner::And),
                    FieldCondition::make($relationField, Operator::Equals, 3, Combiner::And),
                    Query::make([
                        FieldCondition::make($morphRelationField, Operator::Equals, 4, Combiner::And),
                        Query::make([
                            FieldCondition::make($relationField, Operator::Equals, 5, Combiner::And),
                            FieldCondition::make($sixthField, Operator::Equals, 6, Combiner::Or),
                        ], Combiner::Or),
                    ], Combiner::And),
                ], Combiner::And),
            ],
        ];
    }

}
