<?php

declare(strict_types=1);

namespace Egal\Model\With;

use Egal\Model\Filter\FilterPart;

class Collection
{

    /**
     * @var \Egal\Model\With\Relation[]
     */
    private array $relations = [];

    /**
     * @param string[]|mixed[] $array
     * @throws \Egal\Model\Exceptions\FilterException
     * @throws \Egal\Model\Exceptions\InitializeFilterPartException
     */
    public static function fromArray(array $array): self
    {
        $collection = new static();
        $isSequentialArray = is_sequential_array($array);

        foreach ($array as $relationName => $relationContent) {
            if ($isSequentialArray) {
                $relationName = $relationContent;
                unset($relationContent);
            }

            $relation = Relation::fromString($relationName);

            if (isset($relationContent['filter'])) {
                $relation->setFilter(FilterPart::fromArray($relationContent['filter']));
            }

            $collection->relations[] = $relation;
        }

        return $collection;
    }

    /**
     * @return \Egal\Model\With\Relation[]
     */
    public function getRelations(): array
    {
        return $this->relations;
    }

}
