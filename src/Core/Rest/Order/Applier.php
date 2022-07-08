<?php

namespace Egal\Core\Rest\Order;

use Illuminate\Database\Eloquent\Builder;

class Applier
{
    // TODO валидацию сортируемого поля по метаданным модели

    public static function apply(Builder $query, array $orders): Builder
    {
        foreach ($orders as $order) {
            $query->orderBy($order->getColumn(), $order->getDirection()->value);
        }

        return $query;
    }
}
