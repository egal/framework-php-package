<?php

namespace EgalFramework\Model\Tests\Samples\Stubs;

use EgalFramework\Common\Interfaces\RelationDirectionInterface;

class Direction implements RelationDirectionInterface
{
    /** @var string */
    private string $model;

    /** @var string */
    private string $id;

    public function __construct(string $model = '', int $id = 0)
    {
        $this->model = $model;
        $this->id = $id;
    }

    public function setId(int $id): RelationDirectionInterface
    {
        $this->id = $id;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'model' => $this->model,
            'id' => $this->id,
        ];
    }
}
