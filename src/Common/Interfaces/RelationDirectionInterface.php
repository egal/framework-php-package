<?php

namespace EgalFramework\Common\Interfaces;

interface RelationDirectionInterface
{

    public function setId(int $id): RelationDirectionInterface;

    public function toArray(): array;

}
