<?php

namespace Egal\Core\Rest\Filter;

use Egal\Core\Exceptions\FilterSqlOperatorNotFoundException;

enum Operator: string
{

    case Equals = 'eq';
    case NotEquals = 'not eq';
    case GreaterThenOperator = 'gt';
    case LessThenOperator = 'lt';
    case GreaterOrEqualOperator = 'ge';
    case LessOrEqualOperator = 'le';
    case ContainOperator = 'co';
    case StartWithOperator = 'sw';
    case EndWithOperator = 'ew';
    case NotContainOperator = 'not co';
    case EqualIgnoreCaseOperator = 'eqi';
    case ContainIgnoreCaseOperator = 'coi';
    case StartWithIgnoreCaseOperator = 'swi';
    case EndWithIgnoreCaseOperator = 'ewi';
    case NotEqualIgnoreCaseOperator = 'not eqi';
    case NotContainIgnoreCaseOperator = 'not coi';

    public function getSqlOperator(): string
    {
        return match ($this) {
            self::Equals => '=',
            self::NotEquals => '!=',
            self::GreaterThenOperator => '>',
            self::LessThenOperator => '<',
            self::GreaterOrEqualOperator => '>=',
            self::LessOrEqualOperator => '<=',
            self::ContainOperator, self::StartWithOperator, self::EndWithOperator => 'LIKE',
            self::NotContainOperator => 'NOT LIKE',
            self::EqualIgnoreCaseOperator, self::ContainIgnoreCaseOperator, self::StartWithIgnoreCaseOperator, self::EndWithIgnoreCaseOperator => 'ILIKE',
            self::NotEqualIgnoreCaseOperator, self::NotContainIgnoreCaseOperator => 'NOT ILIKE',
        };
    }

}
