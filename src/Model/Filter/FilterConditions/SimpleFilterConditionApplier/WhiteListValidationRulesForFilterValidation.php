<?php

declare(strict_types=1);

namespace Egal\Model\Filter\FilterConditions\SimpleFilterConditionApplier;

enum WhiteListValidationRulesForFilterValidation: string
{

    case ACCEPTED = 'accepted';
    case ACTIVE_URL = 'active_url';
    case NULLABLE = 'nullable';
    case ALPHA_DASH = 'alpha_dash';
    case ALPHA_NUM = 'alpha_num';
    case BOOLEAN = 'boolean';
    case DECLINED = 'declined';
    case EMAIL = 'email';
    case EXISTS = 'exists';
    case INTEGER = 'integer';
    case IP = 'ip';
    case IPV4 = 'ipv4';
    case IPV6 = 'ipv6';
    case JSON = 'json';
    case NOT_REGEX = 'not_regex';
    case NUMERIC = 'numeric';
    case REGEX = 'regex';
    case IN = 'in';
    case NOT_IN = 'not_in';
    case IN_ARRAY = 'in_array';
    case STRING = 'string';
    case ARRAY = 'array';
    case DATE = 'date';
    case URL = 'url';
    case UUID = 'uuid';
    case NUMBER = 'number';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
