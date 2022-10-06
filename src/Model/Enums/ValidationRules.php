<?php

declare(strict_types=1);

namespace Egal\Model\Enums;

enum ValidationRules: string
{

    case SOMETIMES = 'sometimes';
    case BOOLEAN = 'boolean';
    case FLOAT = 'float';
    case ACCEPTED = 'accepted';
    case ACTIVE_URL = 'active_url';
    case ALPHA = 'alpha';
    case ALPHA_DASH = 'alpha_dash';
    case ALPHA_NUM = 'alpha_num';
    case ARRAY = 'array';
    case BAIL = 'bail';
    case CONFIRMED = 'confirmed';
    case DATE = 'date';
    case DECLINED = 'declined';
    case EXCLUDE = 'exclude';
    case FILE = 'file';
    case FILLED = 'filled';
    case IMAGE = 'image';
    case INTEGER = 'integer';
    case IP = 'ip';
    case IPV4 = 'ipv4';
    case IPV6 = 'ipv6';
    case MAC_ADDRESS = 'mac_address';
    case NULLABLE = 'nullable';
    case NUMERIC = 'numeric';
    case PRESENT = 'present';
    case PROHIBITED = 'prohibited';
    case REQUIRED = 'required';
    case STRING = 'string';
    case TIMEZONE = 'timezone';
    case URL = 'url';
    case UUID = 'uuid';

}
