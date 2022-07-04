<?php

declare(strict_types=1);

namespace Egal\Core\Rest\Filter;

use Egal\Core\Exceptions\FilterParseException;

class Parser
{
    private string $conditionRegPattern;
    private string $subQueryRegPattern;
    private string $queryRegPattern;

    public function __construct()
    {
        $operatorsAsStrings = array_map(fn(Operator $operator) => $operator->value, Operator::cases());
        $combinersAsStrings = array_map(fn(Combiner $operator) => $operator->value, Combiner::cases());

        $operatorsPattern = implode('|', $operatorsAsStrings);
        $fieldElementPattern = "((?<field_element>" . Field::REG_PATTERN . ")|(?<relation_field_element>" . RelationField::REG_PATTERN . ")|(?<morph_relation_field_element>" . MorphRelationField::REG_PATTERN . "))";
        $this->conditionRegPattern = "/^(?<field_condition>((?<field_condition_combiner>and|or) )?($fieldElementPattern) (?<operator>$operatorsPattern) (?<value>.+))$/";


        $combinersSimplePattern = implode('|', $combinersAsStrings);
        $this->subQueryRegPattern = "/^(?<combiner>$combinersSimplePattern) \((?<sub_query>.+)\)$/";

        $left = implode(' |', $combinersAsStrings);
        $right = implode('| ', $combinersAsStrings);
        $this->queryRegPattern = "/(?:^|$left ).+?(?= $right|$)/";
    }

    public function parse(?string $queryString, Combiner $combiner = Combiner::And): Query
    {
        $query = Query::make([], $combiner);

        if ($queryString === '' || $queryString === null) {
            return $query;
        }

        preg_match_all($this->queryRegPattern, $queryString, $queryMatches);
        $queryMatches = $queryMatches[0];

        for ($key = 0; $key < count($queryMatches); $key++) {
            if (substr_count($queryMatches[$key], '(') !== substr_count($queryMatches[$key], ')')) {
                $queryMatches[$key + 1] = $queryMatches[$key] . ' ' . $queryMatches[$key + 1];
                unset($queryMatches[$key]);
            }
        }

        foreach ($queryMatches as $match) {
            if (preg_match($this->conditionRegPattern, $match, $conditionMatches)) {
                $notEmptyFieldElementArray = array_filter([
                    $conditionMatches['field_element'],
                    $conditionMatches['relation_field_element'],
                    $conditionMatches['morph_relation_field_element']
                ]);
                $condition = $this->makeFieldConditionFromRaw(
                    array_shift($notEmptyFieldElementArray),
                    $conditionMatches['operator'],
                    $conditionMatches['value'],
                    $conditionMatches['field_condition_combiner']);

                $query->addCondition($condition);
            } elseif (preg_match($this->subQueryRegPattern, $match, $subQueryStringMatches)) {
                $subQuery = $this->parse(
                    $subQueryStringMatches['sub_query'],
                    Combiner::from($subQueryStringMatches['combiner'])
                );
                $query->addCondition($subQuery);
            } else {
                throw new FilterParseException();
            }
        }

        return $query;
    }

    private function makeFieldConditionFromRaw(string $field, string $operator, string $value, string $combiner): FieldCondition
    {
        $operator = Operator::from($operator);

        $value = $this->makeValueFromRaw($value);

        $field = $this->makeFieldFromRaw($field);

        return $combiner === ''
            ? FieldCondition::make($field, $operator, $value)
            : FieldCondition::make($field, $operator, $value, Combiner::from($combiner));
    }

    private function makeValueFromRaw(string $value): string|int|bool|null|float
    {
        if ($value === 'true') {
            $value = true;
        } elseif ($value === 'false') {
            $value = false;
        } elseif ($value === 'null') {
            $value = null;
        } elseif (preg_match("/^'.+'$/", $value)) { // string type
            $value = ltrim(rtrim($value, "'"), "'");
        } elseif (preg_match("/^[1-9]+$/", $value)) { // int type
            $value = intval($value);
        } elseif (preg_match("/^[1-9]+\.[1-9]+$/", $value)) { // float type
            $value = floatval($value);
        } else {
            throw new FilterParseException();
        }
        return $value;
    }

    private function makeFieldFromRaw(string $field): AbstractField
    {
        switch (true) {
            case preg_match("/^" . MorphRelationField::REG_PATTERN . "$/", $field, $matches):
                $types = explode(MorphRelationField::TYPES_DELIMITER, $matches['types']);
                $field = new MorphRelationField($matches['morph_relation_field'], $matches['morph_relation'], $types);
                break;
            case preg_match("/^" . RelationField::REG_PATTERN . "$/", $field, $matches):
                $field = new RelationField($matches['relation_field'], $matches['relation']);
                break;
            case preg_match("/^" . Field::REG_PATTERN . "$/", $field):
                $field = new Field($field);
                break;
            default:
                throw new FilterParseException();
        }

        return $field;
    }

}
