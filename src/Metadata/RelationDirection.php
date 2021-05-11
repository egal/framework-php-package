<?php

namespace EgalFramework\Metadata;

use EgalFramework\Common\Interfaces\RelationDirectionInterface;

class RelationDirection implements RelationDirectionInterface
{

    /** @var string */
    private string $model;

    /** @var string */
    private string $id;

    /**
     * RelationDirection constructor.
     * @param string $model
     * @param int $id
     */
    public function __construct(string $model, int $id = 0)
    {
        $this->model = $model;
        $this->id = $id;
    }

    /**
     * @param int $id
     * @return RelationDirection
     */
    public function setId(int $id): RelationDirectionInterface
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'id' => $this->id,
        ];
    }

}
