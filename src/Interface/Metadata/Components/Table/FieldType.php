<?php

namespace Egal\Interface\Metadata\Components\Table;


enum FieldType: string
{
    case Integer = 'integer';
    case Boolean = 'boolean';
    case Date = 'date';
    case String = 'string';
    case Numeric = 'numeric';
    case Array = 'array';
    case Json = 'json';
    case ArrayOfString = 'string[]';

}
