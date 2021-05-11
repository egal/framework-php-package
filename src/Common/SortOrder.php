<?php

namespace EgalFramework\Common;

class  SortOrder
{

    const ASC = 'asc';
    const DESC = 'desc';

    public static function check(string $order): bool
    {
        return in_array($order, [self::ASC, self::DESC]);
    }

}
