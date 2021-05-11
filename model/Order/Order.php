<?php

namespace Egal\Model\Order;

use Egal\Exception\OrderException;

/**
 * @package Egal\Model
 */
final class Order
{

    private string $column;
    private string $direction = OrderDirectionType::ASC;

    /**
     * Order constructor.
     * @param string $column
     * @param string $direction
     * @throws OrderException
     */
    public function __construct(string $column, string $direction = OrderDirectionType::ASC)
    {
        $this->setColumn($column);
        $this->setDirection($direction);
    }

    public function getColumn(): string
    {
        return $this->column;
    }

    public function setColumn(string $column): void
    {
        $this->column = $column;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @throws OrderException
     */
    public function setDirection(string $direction): void
    {
        if (!in_array($direction, [OrderDirectionType::ASC, OrderDirectionType::DESC])) {
            throw new OrderException('Неверный формат направления!');
        }
        $this->direction = $direction;
    }

    /**
     * @param array $array
     * @return Order|Order[]
     * @throws OrderException
     */
    public static function fromArray(array $array)
    {
        if (array_is_multidimensional($array)) {
            $result = [];
            /** @var array $orderArrayItem */
            foreach ($array as $orderArrayItem) {
                $result[] = Order::fromOrderArray($orderArrayItem);
            }
            return $result;
        }
        return Order::fromOrderArray($array);
    }

    /**
     * @param array $orderArray
     * @return Order
     * @throws OrderException
     */
    private static function fromOrderArray(array $orderArray): Order
    {
        if (array_key_exists('column', $orderArray) && array_key_exists('direction', $orderArray)) {
            return new Order($orderArray['column'], $orderArray['direction']);
        } elseif (array_key_exists(0, $orderArray) && array_key_exists(1, $orderArray)) {
            return new Order($orderArray[0], $orderArray[1]);
        } else {
            throw new OrderException('Неверный формат!');
        }
    }

}
