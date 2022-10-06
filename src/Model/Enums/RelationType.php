<?php

declare(strict_types=1);

namespace Egal\Model\Enums;

enum RelationType: string
{

    case HAS_ONE = 'has_one';
    case HAS_MANY = 'has_many';
    case BELONGS_TO = 'belongs_to';
    case BELONGS_TO_MANY = 'belongs_to_many';
    case HAS_MANY_DEEP = 'has_many_deep';
    case HAS_MANY_THROUGH = 'has_many_through';

}
