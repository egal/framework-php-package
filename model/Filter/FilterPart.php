<?php

namespace Egal\Model\Filter;

use Egal\Exception\FilterException;

/**
 * @package Egal\Model
 */
final class FilterPart
{

    private array $content = [];

    /**
     * @param array $array
     * @return FilterPart
     * @throws FilterException
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
                $filterPart->addContentItem(FilterPart::fromArray($item));
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
     * @param FilterPart|FilterCondition|FilterCombiner $item
     * @throws FilterException
     */
    public function addContentItem($item): void
    {
        if (count($this->content) > 0) {
            $key = array_key_last($this->content);
            if (!($item instanceof FilterCombiner) && !($this->content[$key] instanceof FilterCombiner)) {
                throw new FilterException('Отсутствует объединитель операций!');
            }
        }
        $this->content[] = $item;
    }

}
