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

        if (is_sequential_array($array)) {
            foreach ($array as $relName) {
                $relation = new Relation();
                $relation->setName($relName);
                $collection->relations[] = $relation;
            }
        } else {
            foreach ($array as $relName => $relContent) {
                $relation = new Relation();
                $relation->setName($relName);

                if (isset($relContent['filter'])) {
                    $relation->setFilter(FilterPart::fromArray($relContent['filter']));
                }

                $collection->relations[] = $relation;
            }
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
