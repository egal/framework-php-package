<?php

namespace Egal\Core\Database\Metadata;

enum DataType: string
{
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Date = 'date';
    case String = 'string';
    case Numeric = 'numeric';
    case Array = 'array';
    case Json = 'json';
}
