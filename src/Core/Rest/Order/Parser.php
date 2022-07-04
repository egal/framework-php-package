<?php

namespace Egal\Core\Rest\Order;

use Egal\Core\Exceptions\OrderParseException;

class Parser
{
    public function parse(?string $queryString): array
    {
        $orders = [];

        if ($queryString === '' || $queryString === null) {
            return $orders;
        }

        $ordersRaw = explode(ColumnOrder::ORDERS_DELIMITER, $queryString);

        foreach ($ordersRaw as $order) {
            if (preg_match(ColumnOrder::REG_PATTERN, $order, $matches)) {
                $orders[] = ColumnOrder::make($matches['column'], Direction::from($matches['direction']));
            } else {
                throw new OrderParseException();
            }
        }

        return $orders;
    }
}
