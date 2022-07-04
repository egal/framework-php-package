<?php

namespace Egal\Core\Rest\Order;

class ColumnOrder
{
    public const ORDERS_DELIMITER = ",";
    public const REG_PATTERN = "/(?<direction>(asc|desc))\((?<column>[a-z_]+)\)/m";

    private string $column;
    private Direction $direction;

    public static function make(string $column, Direction $direction = Direction::Asc): static
    {
        $columnOrder = new static();
        $columnOrder->column = $column;
        $columnOrder->direction = $direction;

        return $columnOrder;
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getDirection(): Direction
    {
        return $this->direction;
    }

}
