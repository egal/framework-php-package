<?php

declare(strict_types=1);

namespace Egal\Model\Filter;

use Egal\Model\Exceptions\FilterException;

final class FilterPart
{

    /**
     * @var mixed[]
     */
    private array $content = [];

    /**
     * @param mixed[] $array
     * @throws \Egal\Model\Exceptions\FilterException
     */
    public static function fromArray(array $array): FilterPart
    {
        $filterPart = new FilterPart();

        foreach ($array as $item) {
            if (FilterCondition::mayMakeFromArray($item)) {
                $filterPart->addContentItem(FilterCondition::fromArray($item));
            } elseif (FilterCombiner::mayMakeFromString($item)) {
                $filterPart->addContentItem(FilterCombiner::fromString($item));
            } elseif (is_array($item)) {
                $filterPart->addContentItem(self::fromArray($item));
            }
        }

        return $filterPart;
    }

    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param \Egal\Model\Filter\FilterPart|\Egal\Model\Filter\FilterCondition|\Egal\Model\Filter\FilterCombiner $item
     * @throws \Egal\Model\Exceptions\FilterException
     */
    public function addContentItem($item): void
    {
        if (count($this->content) > 0) {
            $key = array_key_last($this->content);

            if (!($item instanceof FilterCombiner) && !($this->content[$key] instanceof FilterCombiner)) {
                throw new FilterException('Operations combiner is missing!');
            }
        }

        $this->content[] = $item;
    }

}
