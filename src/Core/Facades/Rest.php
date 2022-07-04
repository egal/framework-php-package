<?php

namespace Egal\Core\Facades;

use Egal\Core\Rest\Controller;
use Egal\Core\Rest\Filter\Query as FilterQuery;
use Egal\Core\Rest\Pagination\PaginationParams;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array index(string $modelClass, PaginationParams $pagination, array $scope = [], FilterQuery $filter = null, array $select = [], array $order = [])
 * @method static array create(string $modelClass, array $attributes = [])
 * @method static array show(string $modelClass, $key, array $select = [])
 * @method static array update(string $modelClass, $key, array $attributes = [])
 * @method static void delete(string $modelClass, $key)
 * @method static array metadata(string $modelClass)
 *
 * @see Controller
 */
class Rest extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'egal.rest';
    }

}
