<?php

declare(strict_types=1);

namespace Egal\Model\Order;

use Egal\Model\Exceptions\OrderException;

final class Order
{

    private string $column;

    private string $direction = OrderDirectionType::ASC;

    /**
     * Order constructor.
     *
     * @throws \Egal\Model\Exceptions\OrderException
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
     * @throws \Egal\Model\Exceptions\OrderException
     */
    public function setDirection(string $direction): void
    {
        if (!in_array($direction, [OrderDirectionType::ASC, OrderDirectionType::DESC])) {
            throw new OrderException('Invalid direction format!');
        }

        $this->direction = $direction;
    }

    /**
     * @param mixed[] $array
     * @return \Egal\Model\Order\Order|\Egal\Model\Order\Order[]
     * @throws \Egal\Model\Exceptions\OrderException
     */
    public static function fromArray(array $array)
    {
        if (array_is_multidimensional($array)) {
            $result = [];
            /** @var array $orderArrayItem */
            foreach ($array as $orderArrayItem) {
                $result[] = self::fromOrderArray($orderArrayItem);
            }

            return $result;
        }

        return self::fromOrderArray($array);
    }

    /**
     * @param string[] $orderArray
     * @throws \Egal\Model\Exceptions\OrderException
     */
    private static function fromOrderArray(array $orderArray): Order
    {
        if (array_key_exists('column', $orderArray) && array_key_exists('direction', $orderArray)) {
            return new Order($orderArray['column'], $orderArray['direction']);
        }

        if (array_key_exists(0, $orderArray) && array_key_exists(1, $orderArray)) {
            return new Order($orderArray[0], $orderArray[1]);
        }

        throw new OrderException('Invalid format!');
    }

}
