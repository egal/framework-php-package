<?php

declare(strict_types=1);

namespace Egal\Model\Enums;

// TODO: Переделать на FieldType и AttributeParameterType
enum VariableType: string
{

    case STRING = 'string';
    case INTEGER = 'integer';
    case UUID = 'uuid';
    case DATETIME = 'datetime';
    case DATE = 'date';
    case JSON = 'json';
    case BOOLEAN = 'boolean';
    case NUMERIC = 'numeric';
    case ARRAY = 'array';

}
